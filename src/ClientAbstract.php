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
use zaboy\async\StoreAbstract;
use Zend\Db\Sql\Select;

/**
 * Client
 *
 * @category   async
 * @package    zaboy
 */
abstract class ClientAbstract extends AsyncAbstract
{

    /**
     *
     * @var StoreAbstract
     */
    protected $store;

    /**
     *
     * @var string
     */
    protected $id;

    public function __construct(StoreAbstract $store, $id = null, $data = null)
    {
        $this->store = $store;
        if (!is_null($id) && !$this->isId($id)) {
            $exceptionClass = $this::EXCEPTION_CLASS;
            throw new $exceptionClass('Wrong format $id');
        }
        if (!isset($id)) {
            $entity = $this->makeNewEntity($data);
            $this->id = $entity->getId();
        } else {
            $this->id = $id;
        }
    }

    abstract protected function makeNewEntity($data = null);

    /**
     * Returns an array created from stored in Store data of entity.
     *
     * @return array mixed
     */
    protected function getStoredData($id = null)
    {
        $storeClass = get_class($this->store);
        $id = !$id ? $this->getId() : $id;
        $where = [$storeClass::ID => $id];
        $rowset = $this->store->select($where);
        $data = $rowset->current();
        if (!isset($data)) {
            $exceptionClass = $this::EXCEPTION_CLASS;
            throw new $exceptionClass(
            "There is  not data in store  for promiseId: $id"
            );
        } else {
            return $data->getArrayCopy();
        }
    }

    /**
     * Returns the class name of Entity
     *
     * @return string
     */
    abstract protected function getClass($id = null);

    /**
     * Returns the Entity ID
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    abstract public function toArray();
}
