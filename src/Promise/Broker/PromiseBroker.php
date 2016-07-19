<?php

namespace zaboy\async\Promise\Broker;

use zaboy\scheduler\Callback\Interfaces\CallbackInterface;
use zaboy\async\Promise\PromiseClient;
use zaboy\async\Promise\Interfaces\PromiseBrokerInterface;
use zaboy\async\Promise\Adapter\MySqlPromiseAdapter;
use zaboy\async\Promise\PromiseException;

class PromiseBroker implements PromiseBrokerInterface
{

    const SERVICE_NAME = 'promise_broker';
    const DEFAULT_MAX_TIME_IN_FLIGHT_VALUE = 3600;

    protected $maxTimeInFlight;

    /**
     *
     * @var MySqlPromiseAdapter
     */
    protected $promiseAdapter;

    /**
     *
     *
     * @param MySqlPromiseAdapter $promiseAdapter
     */
    public function __construct(MySqlPromiseAdapter $promiseAdapter)
    {
        $this->promiseAdapter = $promiseAdapter;
        $this->setMaxTimeInFlight();
    }

    public function makePromise()
    {
        $promise = new PromiseClient($this->promiseAdapter);
        return $promise;
    }

    public function getPromise($promiseId)
    {
        if (!isset($promiseId)) {
            throw new PromiseException('Can not run  "PromiseBroker::getPromise(NULL)"');
        }
        $promise = new PromiseClient($this->promiseAdapter, $promiseId);
        return $promise;
    }

    protected function setMaxTimeInFlight($maxTimeInFlight = null)
    {
        $this->maxTimeInFlight = !$maxTimeInFlight ? static::DEFAULT_MAX_TIME_IN_FLIGHT_VALUE : $maxTimeInFlight;
    }

    /**
     *
     * @return int
     */
    protected function getMaxTimeInFlight()
    {
        return $this->maxTimeInFlight;
    }

}
