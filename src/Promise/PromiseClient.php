<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\async\Promise;

use GuzzleHttp\Promise\Promise;
//use GuzzleHttp\Promise\PromiseInterface;
use zaboy\rest\DataStore\Interfaces\DataStoresInterface;
use zaboy\async\Promise\PromiseException;
use zaboy\async\Promise\PromiseAbstract;
use zaboy\async\Promise\Pending\PendingPromise;
use zaboy\async\Promise\Interfaces\PromiseInterface;
use zaboy\async\Promise\PromiseBroker;
use zaboy\async\Promise\Adapter\MySqlPromiseAdapter;
use zaboy\async\Promise\Factory\Adapter\MySqlAdapterFactory;
use zaboy\scheduler\DataStore\UTCTime;
use zaboy\async\Promise\Pending\PendingPromise;

/**
 * PromiseClient
 *
 * @category   async
 * @package    zaboy
 */
class PromiseClient implements PromiseInterface//extends PromiseAbstract//implements PromiseInterface
{

    const PROMISE_ID_PREFIX = 'promise';
    const ID_SEPARATOR = '_';

    /**
     *
     * @var MySqlPromiseAdapter
     */
    public $promiseAdapter;

    /**
     *
     * @var string
     */
    public $promiseId;

    public function __construct(MySqlPromiseAdapter $promiseAdapter, $promiseId = null)
    {
        $this->promiseAdapter = $promiseAdapter;
        if (!isset($promiseId)) {
            $promiseId = $this->makePromise();
        }
        $this->promiseId = $promiseId;
    }

    protected function makePromise()
    {
        $promise = new PendingPromise($this->promiseAdapter);
        $promiseId = $promise->getPromiseId();
        return $promiseId;
    }

    protected function getPromiseData($exceptionIfAbsent = false)
    {
        $where = [MySqlPromiseAdapter::PROMISE_ID => $this->promiseId];
        $rowset = $this->promiseAdapter->select($where);
        $promiseData = $rowset->current();
        if (!isset($promiseData)) {
            if ($exceptionIfAbsent) {
                throw new PromiseException(
                "There is  not data in store  for promiseId: $this->promiseId"
                );
            } else {
                return null;
            }
        } else {
            return $promiseData;
        }
    }

    public function getState()
    {
        $promiseData = $this->getPromiseData(true);
        $state = $promiseData[MySqlPromiseAdapter::STATE];
        return $state;
    }

}
