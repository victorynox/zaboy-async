<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\async\Promise\Pending;

use zaboy\async\Promise\Interfaces\PromiseInterface;
use zaboy\rest\DataStore\Interfaces\DataStoresInterface;
use zaboy\async\Promise\PromiseException;
use zaboy\async\Promise\PromiseAbstract;
use zaboy\async\Promise\Broker\PromiseBroker;
use zaboy\async\Promise\Adapter\MySqlPromiseAdapter;
use zaboy\async\Promise\Factory\Adapter\MySqlAdapterFactory;

/**
 * PromiseAbstract
 *
 * @category   async
 * @package    zaboy
 */
class PendingPromise extends PromiseAbstract
{

    public function __construct(MySqlPromiseAdapter $promiseAdapter, $promiseId = null)
    {
        parent::__construct($promiseAdapter, $promiseId);
        if (isset($this->promiseId)) {
            $state = $this->getState();
            if ($state !== PromiseInterface::PENDING) {
                throw new PromiseException('Can\'t make  PendingPromise. Status in store is: ' . $state);
            } else {
                $this->state = $state;
            }
        } else {
            $this->promiseId = $this->makePromise();
        }
    }

    public function getState()
    {
        return PromiseInterface::PENDING;
    }

    protected function makePromise($addPromiseData = [])
    {
        $itemData = $addPromiseData;
        $itemData[MySqlPromiseAdapter::STATE] = PromiseInterface::PENDING;

        $promiseId = parent::makePromise($itemData);
        return $promiseId;
    }

}
