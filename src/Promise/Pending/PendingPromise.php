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
use zaboy\async\Promise\Adapter\MySqlPromiseAdapter as Store;
use zaboy\async\Promise\Factory\Adapter\MySqlAdapterFactory;
use zaboy\async\Promise\Determined\FulfilledPromise;

/**
 * PromiseAbstract
 *
 * @category   async
 * @package    zaboy
 */
class PendingPromise extends PromiseAbstract
{

    public function setPromiseData()
    {
        parent::setPromiseData();
        $this->promiseData[Store::STATE] = PromiseInterface::PENDING;
    }

    public function resolve($value)
    {
        $this->promiseData[Store::STATE] = PromiseInterface::FULFILLED;
        $this->promiseData[Store::CLASS_NAME] = '\zaboy\async\Promise\Determined\FulfilledPromise';
        $this->promiseData[Store::RESULT] = $value;
        return $this->promiseData;
    }

    protected function writePromise($addPromiseData = [])
    {
        $promiseId = $this->makePromiseId();

        $itemData = $addPromiseData;
        $itemData[MySqlPromiseAdapter::STATE] = PromiseInterface::PENDING;
        $itemData[MySqlPromiseAdapter::PROMISE_ID] = $promiseId;
        $itemData[MySqlPromiseAdapter::TIME_IN_FLIGHT] = $this->promiseAdapter->getUtcTime();

        try {
            $rowsCount = $this->promiseAdapter->insert($itemData);
        } catch (\Exception $e) {
            throw new PromiseException('Can\'t insert item', 0, $e);
        }
        if (!$rowsCount) {
            throw new PromiseException('Can\'t insert item', 0, $e);
        }

        return $promiseId;
    }

}
