<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\async\Message;

use zaboy\async\Message\MessageException;
use zaboy\async\Message\Exception\TimeIsOutException;
use zaboy\async\Message\Message\PendingMessage;
use zaboy\async\Message\Message\Message;
use zaboy\async\Message\Interfaces\MessageInterface;
use zaboy\async\Message\Store;
use zaboy\async\ClientAbstract;
use zaboy\async\Message\Interfaces\ClientInterface;
use Zend\Db\Sql\Select;
use zaboy\async\Queue;
use zaboy\async\Promise\Store as PromiseStore;
use zaboy\async\Promise\Client as PromiseClient;

/**
 * Message
 *
 * @category   async
 * @package    zaboy
 */
class Client extends ClientAbstract implements ClientInterface
{

    /**
     *
     * @var PromiseStore;
     */
    protected $promisesStore;

    /**
     *
     * @var Queue\Client
     */
    protected $queue;

    public function __construct(Queue\Client $queue, $data)
    {
        $store = $queue->getStore()->getMessagesStore();
        $this->promisesStore = $queue->getStore()->getPromisesStore();
        $promise = new PromiseClient($this->promisesStore);
        $data = is_null($data) ?
                [Store::QUEUE_ID => $queue->getId(), Store::PROMISE => $promise->getId()] :
                is_array($data) ?
                        array_merge($data, [Store::QUEUE_ID => $queue->getId(), Store::PROMISE => $promise->getId()]) :
                        $data;

        parent::__construct($store, $data);
        $this->queue = $queue;
    }

    protected function makeEntity($data = null)
    {
        $entity = new Message($data);
        try {
            $data = $entity->getData();
            $rowsCount = $this->store->insert($data);
        } catch (\Exception $e) {
            throw new MessageException('Can\'t insert data. Id: ' . $entity->getId(), 0, $e);
        }
        if (!$rowsCount) {
            throw new MessageException('Can\'t insert data. Id: ' . $entity->getId());
        }
        return $entity;
    }

    /**
     * Returns the class name of Entity
     *
     * @return string
     */
    protected function getClass($id = null)
    {
        return Message::class;
    }

    public function toArray()
    {
        return $this->getStoredData();
    }

    public function getQueue()
    {
        return $this->queue;
    }

    public function getBody()
    {
        return $this->getEntity()->getBody();
    }

    public function getPromise()
    {
        return new PromiseClient($this->promisesStore, $this->getStoredData()[Store::PROMISE]);
    }

}
