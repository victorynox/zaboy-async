<?php

namespace zaboy\test\async\Callback\Example;

use Zend\Db\Adapter\AdapterInterface;
use zaboy\async\Callback\ServicesInitableTrait;
use zaboy\async\Callback\Interfaces\ServicesInitableInterface;

class CallableWithDb implements ServicesInitableInterface
{

    use ServicesInitableTrait;

    /**
     *
     * @var AdapterInterface
     */
    public $dbAdapter;

    public function __construct($dbAdapter)
    {
        $this->dbAdapter = $dbAdapter;
        $this->servicesList = ['dbAdapter' => 'db'];
    }

    public function __invoke($value = null)
    {
        return 'Platform name ' . $this->dbAdapter->getPlatform()->getName();
    }

}
