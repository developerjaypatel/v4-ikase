<?php namespace Module;

class PDOMuffin implements \ArrayAccess {

    protected string $table;

    /**
     * The primary key name.
     * Usually can be inferred via {@link inferPk()}, but you might need to set it by hand.
     * @var string
     */
    public string $pk;

    protected array  $attributes = [];
    public string    $validationErrors;

    public function __construct(string $table, array $fields = []) {
        $this->table      = $table;
        $this->attributes = $fields;
        $this->pk         = $this->inferPk($table);
    }

    protected function inferPk(string $table) {
        if (strpos($table, 'cse_') === 0) {
            return substr($table, 4).'_id';
        } else {
            //TODO: there's probably a couple others prefixes to be covered
            return "{$table}_id";
        }
    }

    public function __set($name, $value) { return $this->attributes[$name] = $value; }
    public function __get($name) { return $this->attributes[$name]; }
    public function __isset($name) { return isset($this->attributes[$name]); }
    public function __unset($name) { unset($this->attributes[$name]); }

    public function offsetExists($offset) { return $this->__isset($offset); }
    public function offsetGet($offset) { return $this->__get($offset); }
    public function offsetSet($offset, $value) { $this->__set($offset, $value); }
    public function offsetUnset($offset) { $this->__unset($offset); }

    public function getTable() { return $this->table; }
    public function getAttributes() { return $this->attributes; }

    public function setKey(int $value):int { return $this->{$this->pk} = $value; }
    public function getKey():?int { return $this->{$this->pk} ?? null; }
    public function getKeyPair():array { return [$this->pk => $this->getKey()]; }
}
