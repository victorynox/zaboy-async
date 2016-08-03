<?php

namespace zaboy\async\Promise;

use zaboy\async\Promise\Promise;
use zaboy\async\Promise\Store;
use zaboy\async\Promise\PromiseException;

class Broker
{

    const DEFAULT_TIME_LIFE = 3600;

    protected $timeLife;

    /**
     *
     * @var Storer
     */
    protected $store;

    /**
     *
     *
     * @param Storer $store
     */
    public function __construct(Store $store, $timeLife = null)
    {
        $this->store = $store;
        $this->setTimeLife($timeLife);
    }

    public function makePromise()
    {
        $promise = new Promise($this->store);
        return $promise;
    }

    public function getPromise($promiseId)
    {
        if (!isset($promiseId)) {
            throw new PromiseException('Can not run  "Broker::getPromise(NULL)"');
        }
        $promise = new Promise($this->store, $promiseId);
        return $promise;
    }

    public function deletePromise($promiseId)
    {
        if (!isset($promiseId)) {
            throw new PromiseException('Can not run  "Broker::deletePromise(NULL)"');
        }
        $number = $this->store->delete([Store::PROMISE_ID => $promiseId]);
        return (bool) $number;
    }

    protected function setTimeLife($timeLife = null)
    {
        $this->timeLife = !$timeLife ? static::DEFAULT_TIME_LIFE : $timeLife;
    }

    /**
     *
     * @return int
     */
    protected function getTimeLife()
    {
        return $this->timeLife;
    }

}
