<?php namespace Module;

class ExtendedDb extends \Codeception\Module\Db {

    /**
     * Used to clean up entries manually inserted. There's no need to use this with {@link haveInDatabase}, both use
     * the same mechanism.
     * @param string $table
     * @param int    $id
     * @param string $pk
     */
    public function scheduleRemovalFromDatabase(string $table, int $id, $pk = 'id') {
        $this->insertedRows[] = [
            'table' => $table,
            'primary' => [$pk => $id]
        ];
    }

    /**
     * You know what it does. HANDLE WITH CARE. Obviously, will cause trouble if you try to truncate a table with
     * entries used as FKs elsewhere.
     * @param string $table
     */
    public function truncateInDatabase(string $table) {
        $this->_getDbh()->exec("TRUNCATE $table");
    }

    public function countInDatabase($table, array $criteria = []) {
        return parent::countInDatabase($table, $criteria);
    }

}
