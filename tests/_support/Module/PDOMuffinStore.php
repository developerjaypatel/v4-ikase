<?php namespace Module;

class PDOMuffinStore
    extends \League\FactoryMuffin\Stores\AbstractStore
    implements \League\FactoryMuffin\Stores\StoreInterface {

    protected \PDO $pdo;

    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function find(string $table, int $id): PDOMuffin {
        $pk = (new PDOMuffin($table))->pk;
        $stmt = $this->pdo->prepare("SELECT * FROM `$table` WHERE `$pk` = ?");
        $stmt->execute([$id]);
        return new PDOMuffin($table, $stmt->fetch(\PDO::FETCH_ASSOC));
    }

    /**
     * @inheritDoc
     * @param PDOMuffin $model
     */
    protected function save($model) {
        $original_attrs = $model->getAttributes();
        $keys           = array_keys($original_attrs);
        $fields         = implode(', ', array_map(fn($key) => "`$key`", $keys));
        $snake_cased    = array_map(fn($key) => strtr($key, ' ', '_'), $keys);
        $attrs          = array_combine($snake_cased, $original_attrs);
        $params         = implode(', ', array_map(fn($key) => ":$key", $snake_cased));

        try {
            $sql = "INSERT INTO {$model->getTable()} ($fields) VALUES ($params)";
            $this->pdo->prepare($sql)->execute($attrs);
            $id = $this->pdo->lastInsertId();
            $model->setKey($id);
            return true;
        } catch (\PDOException $e) {
            $model->validationErrors = $e->getMessage();
            return false;
        }
    }

    /**
     * @inheritDoc
     * @param PDOMuffin $model
     * @throws \PDOException
     */
    protected function delete($model) {
        if (isset($model->id)) {
            $field = 'id';
            $value = $model->id;
        } elseif ($value = $model->getKey()) {
            $field = $model->pk;
        } else {
            // needed in some crazy tables with no PK ¯\_(ツ)_/¯
            $attr  = $model->getAttributes();
            $field = key($attr);
            $value = current($attr);
        }
        $this->pdo->exec("DELETE FROM {$model->getTable()} WHERE `$field` = '$value' LIMIT 1");
        return true;
    }
}
