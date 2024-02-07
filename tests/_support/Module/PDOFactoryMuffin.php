<?php namespace Module;

class PDOFactoryMuffin extends \League\FactoryMuffin\FactoryMuffin {

    /**
     * @inheritdoc
     * @var PDOMuffinStore
     */
    protected $store;

    /**
     * Works a bit differently from the original method: $modifier allows you to call specific things in the generated object; for instance, changing the default PK.
     * @param string        $class
     * @param callable|null $modifier
     * @return PDOMuffin
     */
    protected function makeClass($class, callable $modifier = null) {
        $obj = new PDOMuffin($class);
        if ($modifier) {
            $modifier($obj);
        }
        return $obj;
    }

    public function find(string $table, int $id): PDOMuffin {
        return $this->store->find($table, $id);
    }

}
