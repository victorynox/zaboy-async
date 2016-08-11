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

    public function __construct(StoreAbstract $store, $data = null)
    {
        $this->store = $store;

        if (!is_array($data) && !empty($data) && !$this->isId($data)) {
            $exceptionClass = $this::EXCEPTION_CLASS;
            throw new $exceptionClass('Wrong format $data');
        }
        if ($this->isId($data)) {
            $this->id = $data;
            return;
        }
        if (is_array($data) || empty($data)) {
            $entity = $this->makeEntity($data);
            $this->id = $entity->getId();
        }
    }

    abstract protected function makeEntity($data = null);

    /**
     * Returns an array created from stored in Store data of entity.
     *
     * @return array mixed
     */
    protected function getStoredData($id = null)
    {
        $id = !$id ? $this->getId() : $id;
        $data = $this->store->read($id);
        if (empty($data)) {
            throw new PromiseException(
            "There is  not data in store  for id: $id"
            );
        } else {
            return $data;
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
