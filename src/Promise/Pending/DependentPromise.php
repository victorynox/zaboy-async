<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\async\Promise;

use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Promise\PromiseInterface;
use zaboy\rest\DataStore\Interfaces\DataStoresInterface;
use zaboy\async\Promise\PromiseException;
use zaboy\async\Promise\Broker\PromiseBroker;
use zaboy\async\Promise\Adapter\MySqlPromiseAdapter;
use zaboy\async\Promise\Factory\Adapter\MySqlAdapterFactory;

/**
 * PromiseAbstract
 *
 * @category   async
 * @package    zaboy
 */
class DependentPromise //implements PromiseInterface
{

    public $promiseId;
    public $state;
    public $result;
    public $endingTime;
    //public $cancelFn;
    //public $waitFn;
    //
    //if this Promise is dependent
    public $parentId;
    public $onParentFulfilled;
    public $onParentRegected;

    /**
     *
     * @param MySqlPromiseAdapter $promiseAdapter
     * @throws PromiseException
     */
    public function __construct(MySqlPromiseAdapter $promiseAdapter, $promiseId)
    {
        $this->promiseAdapter = $promiseAdapter;
        $this->promiseId = $promiseId;
    }

}
