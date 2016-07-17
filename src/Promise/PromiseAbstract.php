<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\async\Promise;

use GuzzleHttp\Promise\Promise;
use zaboy\async\Promise\Interfaces\PromiseInterface;
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
class PromiseAbstract implements PromiseInterface
{

    public $promiseId;
    public $state;

    /**
     *
     * @var string unique id of promise: promise_id_123456789qwerty
     */
    public $promiseId;

    /**
     *
     * @var MySqlPromiseAdapter
     */
    public $promiseAdapter;

    /**
     *
     * @param MySqlPromiseAdapter $promiseAdapter
     * @throws PromiseException
     */
    public function __construct(MySqlPromiseAdapter $promiseAdapter, $promiseId = null)
    {
        $this->promiseAdapter = $promiseAdapter;
        $this->promiseId = $promiseId;
    }

    protected function makePromiseId()
    {
        $time = UTCTime::getUTCTimestamp(0, 6); //Grivich UTC time
        $idWithDot = uniqid(
                self::PROMISE_ID_PREFIX . self::ID_SEPARATOR . self::ID_SEPARATOR
                . $time . self::ID_SEPARATOR . self::ID_SEPARATOR
                , true
        );
        $promiseId = str_replace('.', self::ID_SEPARATOR, $idWithDot);

        return $promiseId;
    }

    protected function makeTimeEnd($maxLifeTime = 3600)
    {
        return (int) UTCTime::getUTCTimestamp(0, 0) + $maxLifeTime;
    }

    public function getPromiseId()
    {
        return $this->promiseId;
    }

    public function getState()
    {
        $where = [MySqlPromiseAdapter::PROMISE_ID => $this->promiseId];
        $rowset = $this->promiseAdapter->select($where);
        $row = $rowset->current();
        if (isset($row)) {
            return $row[MySqlPromiseAdapter::STATE];
        } else {
            throw new PromiseException(
            "Pomise $promiseId is not exist"
            );
        }
    }

    protected function makePromise($addPromiseData = [])
    {
        $itemData = $addPromiseData;

        $promiseId = $this->makePromiseId();
        $itemData[MySqlPromiseAdapter::PROMISE_ID] = $promiseId;

        $timeEnd = $this->makeTimeEnd();
        $itemData[MySqlPromiseAdapter::ACTUAL_TIME_END] = $timeEnd;

        $tableGateway = $this->promiseAdapter;
        try {
            $rowsCount = $tableGateway->insert($itemData);
        } catch (\Exception $e) {
            throw new PromiseException('Can\'t insert item', 0, $e);
        }
        if (!$rowsCount) {
            throw new PromiseException('Can\'t insert item', 0, $e);
        }
        return $promiseId;
    }

}
