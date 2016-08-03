<?php

namespace zaboy\async\Promise;

use zaboy\async\Promise\Promise;
use zaboy\async\Promise\Store;
use zaboy\async\Promise\PromiseException;

class Broker
{

    /**
     * default max time Promise in Store  (in sec)
     */
    const DEFAULT_LIFE_TIME = 3600;

    /**
     *
     * @var int max time Promise in Store  (in sec)
     */
    protected $lifeTime;

    /**
     *
     * @var Store
     */
    protected $store;

    /**
     *
     *
     * @param Store $store
     */
    public function __construct(Store $store, $lifeTime = null)
    {
        $this->store = $store;
        $this->setTimeLife($lifeTime);
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
