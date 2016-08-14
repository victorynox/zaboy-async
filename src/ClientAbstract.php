<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\async;

use zaboy\async\Promise\PromiseException;
use zaboy\async\StoreAbstract;

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
    protected function getStoredData($id = null, $lockRow = false)
    {
        $id = !$id ? $this->getId() : $id;
        $data = $lockRow ? $this->store->readAndLock($id) : $this->store->read($id);
        if (empty($data)) {
            throw new PromiseException(
            "There is  not data in store  for id: $id"
            );
        } else {
            foreach ($data as $key => $value) {
                if ($key !== StoreAbstract::ID && $this->isId($value)) {
                    $data[$key] = new static($this->store, $value);
                }
            }
            return $data;
        }
    }

    protected function runTransaction($methodName, $param1 = null, $params2 = null)
    {
        $store = $this->store;
        try {
            $errorMsg = "Can't start transaction for $methodName";
            $this->store->beginTransaction();
            //is row with this index exist?
            $errorMsg = "Can't readAndLock for $this->id";
            $data = $this->getStoredData($this->id, true);
            $errorMsg = "Can not execute $methodName. Entity is not exist.";
            if (is_null($data)) {
                throw new \Exception("Entity is not exist in Store.");
            }
            $entityClass = $this->getClass();
            $entity = new $entityClass($data);
            $errorMsg = "Can not execute $methodName. Class: $entityClass";
            $dataReturned = call_user_func([$entity, $methodName], $param1, $params2);
            if (!is_null($dataReturned)) {
                $errorMsg = "Can not store->update.";
                $id = $dataReturned[StoreAbstract::ID];
                unset($dataReturned[StoreAbstract::ID]);
                //or update
                $number = $this->store->update($dataReturned, [StoreAbstract::ID => $id]);
                if (!$number) {
                    //or create new if absent
                    $dataReturned[StoreAbstract::ID] = $id;
                    $this->store->insert($dataReturned);
                }
            } else {
                $dataReturned = $data;
            }

            $this->store->commit();
        } catch (\Exception $e) {
            $this->store->rollback();
            $exceptionClass = $this::EXCEPTION_CLASS;
            throw new $exceptionClass($errorMsg . ' Id: ' . $this->id, 0, $e);
        }
        return $id;
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

    /*
     * Returns the Entity as array with unserialized properties
     *
     */

    abstract public function toArray();

    public function getStore()
    {
        return $this->store;
    }

}
