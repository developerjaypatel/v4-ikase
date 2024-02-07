<?php
//FIXME: usage of direct hostnames should be discouraged, to avoid test server accessing prod db, and to enable local development
define('DB_LOCALHOST', empty($_ENV['DOCKER'])? 'localhost' : 'db');

abstract class DB {

    const DB_LOCALHOST     = DB_LOCALHOST;
    const DB_IKASE_ORG     = 'ikase.org';
    const DB_IKASE_WEBSITE = 'ikase.website';
    const DB_MD_REMINDER   = 'md_reminder';
    const DB_REMIND        = 'remind';
    const DB_IP_52         = 'ip_52';
    const DB_IP_54         = 'ip_54';
    const DB_CASEUSER      = 'caseuser';
    const DB_INFOACCO      = 'infoacco';
    const DB_INFOACCO_COL  = 'infoacco_collections';
    const DB_SIMPLICITY    = 'simple_collections';

    /**
     * The default fetch mode is Object. This constant exists so it can be changed for multiple places at once.
     */
    const DEFAULT_FETCH = PDO::FETCH_OBJ;

    private static $lastInsertId = null;

    private static function dbParams($db) {
        static $main, $options;
        if (!$options || !$main) { //never really gonna test !$main, but did it anyway to shut up PHPStorm
            $main    = [
                'host' => isset($_ENV['DOCKER']) && $_ENV['DOCKER']? 'db' : 'localhost',
                'user' => 'root',
                'pwd'  => 'admin527#',
                'db'   => 'ikase',
            ];
            $infoacco = array_merge($main, [
                'user' => 'infoacco_dripper',
                'pwd'  => 'Little10!',
                'db'   => 'infoacco_developer',
            ]);

            $options = [
                self::DB_LOCALHOST     => $main,
                self::DB_IKASE_ORG     => array_merge($main, ['host' => 'ikase.org']),
                self::DB_IKASE_WEBSITE => array_merge($main, ['host' => 'ikase.website']),

                self::DB_MD_REMINDER => array_merge($main, ['host' => 'ikase.org', 'db' => 'md_reminder']),
                self::DB_REMIND      => array_merge($main, ['host' => 'ikase.org', 'db' => 'remind']),
                self::DB_IP_52       => array_merge($main, ['host' => '52.34.166.217']),
                self::DB_IP_54       => array_merge($main, ['host' => '54.149.211.191']),
                self::DB_CASEUSER    => array_merge($main, [
                    'user' => 'gtg_caseuser',
                    'pwd'  => 'thecase',
                    'db'   => 'gtg_thecase',
                ]),
                
                self::DB_INFOACCO     => $infoacco,
                self::DB_INFOACCO_COL => array_merge($infoacco, ['db' => 'infoacco_collections']),
                self::DB_SIMPLICITY   => array_merge($infoacco, [
                        'user' => 'simple_dripper',
                        'db'   => 'simple_collections',
                ])
            ];
        }

        if (!array_key_exists($db, $options)) {
            //TODO: is there any extra value if we throw an exception here instead?
            trigger_error("Invalid DB requested ($db)", E_USER_ERROR);
        } else {
            return $options[$db];
        }
    }

    /**
     * Concentrates all database connection settings in a single function, while still letting each sub-project have their
     * own getConnection() function, to avoid repetition and possible mistakes of repeating the db connection to be used.
     * It also "caches" the db connections, so subsequent calls with the same arguments retrieve the existing connection
     * object instead.
     * @param string $db                   Must be one of the DB::DB_* constants.
     * @param bool   $enableUserDataSource Enables use of $_SESSION['user_data_source'], that is appended to the database name.
     * @return PDO
     * @todo is it possible to intenalize this method? This way we avoid exposing the PDO connection directly
     */
    static function conn($db = self::DB_LOCALHOST, $enableUserDataSource = false):PDO {
        $params = self::dbParams($db);

        $key = $db.($enableUserDataSource? '_user' : '');
        static $cache = [];
        if (isset($cache[$key])) {
            return $cache[$key];
        }

        if ($enableUserDataSource && !empty($_SESSION['user_data_source'])) {
            $params['db'] .= "_{$_SESSION['user_data_source']}";
        }

        $cache[$key] = new PDO("mysql:host={$params['host']};dbname={$params['db']}", $params['user'], $params['pwd']);
        $cache[$key]->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $cache[$key]->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, self::DEFAULT_FETCH);
        return $cache[$key];
    }

    /**
     * Returns the "selected" database connection. Returns the default {@link self::DB_LOCALHOST} unless getConnection()
     * is specified - then, it should return something else but still using DB::conn().
     * @return PDO
     */
    private static function connection(): PDO {
        return function_exists('getConnection')? getConnection() : DB::conn();
    }

    /**
     * Syntactic sugar for PDO's prepare, bindParam, and execute. Depends on a defined getConnection() if you need to
     * use a connection other than the default {@link self::DB_LOCALHOST}.
     * @param string $query
     * @param array|mixed $bindings Allows a single binding, 0-indexed, or associative bindings (different from
     *                              {@link PDO::bindParam()} and {@link PDOStatement::execute()}, as they are
     *                              respectively 1-indexed or associative-only). Also allows the value to be an array,
     *                              what causes the given clause to use "IN" instead of "=".
     * @return PDOStatement
     */
    static function run(string $query, $bindings = []): PDOStatement {
        self::$lastInsertId = null; //make sure failed but silenced queries won't mix up the last INSERT id

        $stmt = self::connection()->prepare($query);
        //walks through the bindings and turns our 0-indexed array into 1-indexed PDO bind params
        $bindings = (array)$bindings;
        array_walk($bindings, function ($value, $bind) use ($stmt) {
            $key = is_int($bind)? ++$bind : $bind;
            if (is_array($value)) {
                foreach ($value as $i => $v) {
                    $stmt->bindValue("{$key}_$i", $v);
                }
            } else {
                $stmt->bindValue($key, $value);
            }
        });

        $stmt->execute();

        if (stripos(trim($query), 'INSERT') === 0) {
            self::$lastInsertId = self::connection()->lastInsertId();
        }

        return $stmt;
    }

    /**
     * Syntatic sugar for the old idiom "mysql_query() or die()", with more relaxed bindings.
     * @param string $query
     * @param array|mixed $bindings Allows a single binding, 0-indexed, or associative bindings (different from
     *                              {@link PDO::bindParam()} and {@link PDOStatement::execute()}, as they are
     *                              respectively 1-indexed or associative-only).
     * @return PDOStatement
     * @see run()
     * @see runOrApiError()
     * @deprecated you're probably interested in {@link runOrApiError()}, as everything around here is API based
     */
    static function runOrDie(string $query, $bindings = []) {
        try {
            return self::run($query, $bindings);
        }
        catch (PDOException $e) {
            die('unable to run query<br/>'.(IS_PROD? '' : $e->__toString()));
        }
    }

    /**
     * Syntatic sugar for the old idiom "try..sql..catch..print json_encode()", with more relaxed bindings.
     * @param array|mixed $bindings Allows a single binding, 0-indexed, or associative bindings (different from
     *                              {@link PDO::bindParam()} and {@link PDOStatement::execute()}, as they are
     *                              respectively 1-indexed or associative-only).
     * @return PDOStatement|void Dies in case of errors.
     * @see run()
     * @see apiCatch()
     * @see runOrDie()
     * @param string $query
     */
    static function runOrApiError(string $query, $bindings = []) {
        try {
            return self::run($query, $bindings);
        }
        catch (PDOException $e) {
            \Api\JsonErrorRenderer::simple($e);
        }
    }

    /**
     * Gets the ID generated in the last statement, if it was an INSERT. Otherwise, null.
     * @see PDO::lastInsertId()
     * @return int|null
     */
    static function lastInsertId(): ?int {
        return self::$lastInsertId;
    }

    /**
     * Creates an INSERT statement.
     * @param string $table
     * @param array  $fields Associative list of fields and values.
     * @return string
     */
    private static function makeInsert(string $table, array $fields): string {
        $keys = array_keys($fields);
        $fields = implode(', ', array_map(fn ($key) => "`$key`", $keys));
        $params = implode(', ', array_map(fn ($key) => ":$key", $keys));
        return "INSERT INTO $table ($fields) VALUES ($params)";
    }

    /**
     * Runs a simplified INSERT.
     * @param string $table
     * @param array  $fields Associative list of fields and values.
     * @return int The inserted ID - null in case of failures.
     */
    static function insert(string $table, array $fields): ?int {
        self::run(self::makeInsert($table, $fields), $fields);
        return self::lastInsertId();
    }

    /**
     * Runs a simplified INSERT, with an old die() if it fails.
     * @param string $table
     * @param array  $fields Associative list of fields and values.
     * @return int The inserted ID - null in case of failures (that should only happen in test scenarios).
     * @deprecated
     */
    static function insertOrDie(string $table, array $fields): ?int {
        self::runOrDie(self::makeInsert($table, $fields), $fields);
        return self::lastInsertId();
    }

    /**
     * Runs a (complete) SELECT query and fetches the result.
     * @param string $query
     * @param array  $bindings
     * @param int    $mode
     * @return mixed
     * @see selectOrError
     */
    static function select(string $query, $bindings = [], $mode = self::DEFAULT_FETCH) {
        return DB::run($query, $bindings)->fetchAll($mode);
    }

    /**
     * Runs a (complete) SELECT query and fetches the result.
     * @param string $query
     * @param array  $bindings
     * @param int    $mode
     * @return mixed
     * @see select
     * @todo might need an extra $json = true attribute, like deleteOrError()
     */
    static function selectOrError(string $query, $bindings = [], $mode = self::DEFAULT_FETCH) {
        return DB::runOrApiError($query, $bindings)->fetchAll($mode);
    }

    //TODO: include a way to run != or NOT IN (maybe using ['!id' => 10]?)
    //TODO: include a way to use other comparisons (maybe using ['id >' => 10]?)
    private static function makeWhere(array $clause): string {
        if (!$clause) {
            throw new InvalidArgumentException('Empty WHERE clause');
        }
        foreach (array_keys($clause) as $key) {
            if (is_numeric($key)) {
                throw new InvalidArgumentException('WHERE clauses must be field => value arrays');
            }
        }

        $keys = [];
        foreach ($clause as $key => $value) {
            if (is_array($value)) {
                $values = implode(',', array_map(fn ($i) => ":{$key}_$i", array_keys($value)));
                $keys[] = "`$key` IN ($values)";
            } else {
                $keys[] = "`$key` = :$key";
            }
        }
        return implode(' AND ', $keys);
    }

    /**
     * Creates an UPDATE statement.
     * @param string $table
     * @param array  $fields Associative list of fields and values to go in SET.
     * @param array  $clause Associative list of fields and values to go in WHERE. Also allows the value to be an array,
     *                       what causes the given clause to use "IN" instead of "=".
     * @param bool   $limit  Adds a LIMIT 1 to the query.
     * @return string
     */
    private static function makeUpdate(string $table, array $fields, array $clause, bool $limit): string {
        $set = implode(', ', array_map(fn ($key) => "`$key` = :$key", array_keys($fields)));
        $where = self::makeWhere($clause);
        return "UPDATE $table SET $set WHERE $where".($limit? ' LIMIT 1' : '');
    }

    /**
     * Runs a simplified UPDATE.
     * @param string $table
     * @param array  $fields Associative list of fields and values to go in SET.
     * @param array  $clause Associative list of fields and values to go in WHERE. Also allows the value to be an array,
     *                       what causes the given clause to use "IN" instead of "=".
     * @param bool   $limit  Adds a LIMIT 1 to the query.
     * @return int The number of affected rows.
     */
    static function update(string $table, array $fields, array $clause, bool $limit = false): int {
        return self::run(self::makeUpdate($table, $fields, $clause, $limit), $fields + $clause)->rowCount();
    }

    /**
     * Runs a simplified UPDATE, with an old die() if it fails.
     * @param string $table
     * @param array  $fields Associative list of fields and values to go in SET.
     * @param array  $clause Associative list of fields and values to go in WHERE. Also allows the value to be an array,
     *                       what causes the given clause to use "IN" instead of "=".
     * @param bool   $limit  Adds a LIMIT 1 to the query.
     * @return int The number of affected rows.
     * @deprecated
     */
    static function updateOrDie(string $table, array $fields, array $clause, bool $limit = false): int {
        $result = self::runOrDie(self::makeUpdate($table, $fields, $clause, $limit), $fields + $clause);
        return $result instanceof PDOStatement? $result->rowCount() : 0;
    }

    /**
     * Runs a simplified DELETE.
     * @param string $table
     * @param array  $clause Associative list of fields and values to go in WHERE. Also allows the value to be an array,
     *                       what causes the given clause to use "IN" instead of "=".
     * @return int The number of affected rows.
     * @todo add a $limit argument, like update
     */
    static function delete(string $table, array $clause): int {
        return self::run("DELETE FROM $table WHERE ".self::makeWhere($clause), $clause)->rowCount();
    }

    /**
     * Runs a simplified DELETE, with an old die() if it fails.
     * @param string $table
     * @param array  $clause Associative list of fields and values to go in WHERE. Also allows the value to be an array,
     *                       what causes the given clause to use "IN" instead of "=".
     * @param bool   $json   If false, fallbacks to old string dying behavior from legacy code, instead of using JSON.
     * @return int The number of affected rows.
     * @todo add a $limit argument, like update
     */
    static function deleteOrError(string $table, array $clause, bool $json = true): int {
        $method = $json? 'runOrApiError' : 'runOrDie';
        $result = self::$method("DELETE FROM $table WHERE ".self::makeWhere($clause), $clause);
        return $result instanceof PDOStatement? $result->rowCount() : 0;
    }
}
