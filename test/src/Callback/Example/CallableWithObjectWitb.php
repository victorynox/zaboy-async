<?php

namespace zaboy\test\async\Callback\Example;

use Zend\Db\Adapter\AdapterInterface;
use zaboy\async\Callback\ServicesInitableTrait;
use zaboy\async\Callback\Interfaces\ServicesInitableInterface;

class CallableWithObjectWitb implements ServicesInitableInterface
{

    use ServicesInitableTrait;

    /**
     *
     * @var CallableWithDb
     */
    public $object;

    public function __construct($dbAdapter)
    {
        $this->object = new CallableWithDb($dbAdapter);
    }

    public function __invoke($value = null)
    {
        return 'Platform name ' . $this->object->dbAdapter->getPlatform()->getName();
    }

}
