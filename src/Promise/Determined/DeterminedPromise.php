<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\async\Promise\Determined;

use zaboy\rest\DataStore\Interfaces\DataStoresInterface;
use zaboy\async\Promise\PromiseException;
use zaboy\async\Promise\Broker\PromiseBroker;
use zaboy\async\Promise\Adapter\MySqlPromiseAdapter;
use zaboy\async\Promise\Factory\Adapter\MySqlAdapterFactory;
use zaboy\async\Promise\PromiseAbstract;

/**
 * PromiseAbstract
 *
 * @category   async
 * @package    zaboy
 */
abstract class DeterminedPromise extends PromiseAbstract
{

    /**
     *
     * @param MySqlPromiseAdapter $promiseAdapter
     * @throws PromiseException
     */
    public function __construct(MySqlPromiseAdapter $promiseAdapter, $promiseId)
    {
        parent::__construct($promiseAdapter, $promiseId);
    }

}
