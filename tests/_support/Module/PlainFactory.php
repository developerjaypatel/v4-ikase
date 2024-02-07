<?php namespace Module;

use Codeception\Exception\ModuleException;

/**
 * This is all sort of a workaround as FactoryMuffin v3 doesn't allow non-model uses (v2 did).
 * @package Module
 */
class PlainFactory extends \Codeception\Module\DataFactory {

    protected $config = ['factories' => 'tests/_factories'];

    /** @var ExtendedDb */
    public $ormModule;

    public function _depends() {
        //TODO: should depend on DB, but we can't override _inject() because of the typehint, and dependency config seems tricky with an overridden class anyway
        return [];
    }

    public function _inject($orm) { }

    public function _initialize() {
        //FIXME: dirty dependency injection override :shrug:
        $this->ormModule = $this->getModule('\Module\ExtendedDb');
    }

    protected function getStore() {
        return new PDOMuffinStore($this->ormModule->_getDbh());
    }

    /**
     * @inheritDoc
     * @param string $name
     * @param array  $extraAttrs
     * @return PDOMuffin|object
     */
    public function have($name, array $extraAttrs = []) {
        return parent::have($name, $extraAttrs);
    }

    /**
     * @inheritDoc
     * @param string $name
     * @param int    $times
     * @param array  $extraAttrs
     * @return PDOMuffin[]|object[]
     */
    public function haveMultiple($name, $times, array $extraAttrs = []) {
        return parent::haveMultiple($name, $times, $extraAttrs);
    }

    public function _beforeSuite($settings = []) {
        $store = $this->getStore();
        $this->factoryMuffin = new PDOFactoryMuffin($store);

        if ($this->config['factories']) {
            foreach ((array) $this->config['factories'] as $factoryPath) {
                $realpath = realpath(codecept_root_dir().$factoryPath);
                if ($realpath === false) {
                    throw new ModuleException($this, "The path to one of your factories is not correct. Please specify the directory relative to the codeception.yml file (ie. _support/factories).");
                }
                $this->factoryMuffin->loadFactories($realpath);
            }
        }
    }

}
