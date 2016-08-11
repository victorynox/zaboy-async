<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\async;

use zaboy\async\Promise\PromiseException;
use zaboy\async\Promise\Exception\TimeIsOutException;
use zaboy\async\Promise\Promise\PendingPromise;
use zaboy\async\Promise\PromiseAbstract;
use zaboy\async\ClientInterface;
use zaboy\async\Store;
use Zend\Db\Sql\Select;
use zaboy\async\ClientAbstract;

/**
 * EntityAbstract
 *
 * @category   async
 * @package    zaboy
 */
abstract class EntityAbstract extends AsyncAbstract
{

}
