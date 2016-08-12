<?php

namespace zaboy\async;

use zaboy\async\Promise\Client;
use zaboy\async\Promise\Store;
use zaboy\async\Promise\PromiseException;

class BrokerAbstract
{

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
    public function __construct(Store $store)
    {
        $this->store = $store;
    }

    public function make()
    {
        $promise = new Client($this->store);
        return $promise;
    }

    public function get($id)
    {
        if (!isset($id)) {
            throw new \Exception('Can not run  "Broker::get(NULL)"');
        }
        $client = new Client($this->store, $id);
        return $client;
    }

    public function delete($id)
    {
        if (!isset($id)) {
            throw new \Exception('Can not run  "Broker::delete(NULL)"');
        }
        $number = $this->store->delete([Store::ID => $id]);
        return (bool) $number;
    }

}
