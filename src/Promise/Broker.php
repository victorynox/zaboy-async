<?php

namespace zaboy\async\Promise;

use zaboy\async\Promise\Client;
use zaboy\async\Promise\Store;
use zaboy\async\BrokerAbstract;
use zaboy\async\Promise\PromiseException;

class Broker extends BrokerAbstract
{

    /**
     * default max time Promise in Store  (in sec)
     */
    const DEFAULT_LIFE_TIME = 3600;

    /**
     *
     * @var int max life time of Promise in the Store  (in sec)
     */
    protected $lifeTime;

    /**
     *
     *
     * @param Store $store
     */
    public function __construct(Store $store, $lifeTime = null)
    {
        parent::__construct($store);
        $this->setTimeLife($lifeTime);
    }

    protected function setTimeLife($lifeTime = null)
    {
        $this->lifeTime = !$lifeTime ? static::DEFAULT_LIFE_TIME : $lifeTime;
    }

    /**
     *
     * @return int
     */
    protected function getTimeLife()
    {
        return $this->lifeTime;
    }

}
