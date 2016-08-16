<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\async\Queue;

use zaboy\async\Queue\QueueException;
use zaboy\async\Queue\Exception\TimeIsOutException;
use zaboy\async\Queue\Queue\PendingQueue;
use zaboy\async\Queue\Queue\Queue;
use zaboy\async\Queue\Interfaces\QueueInterface;
use zaboy\async\Queue\Store;
use zaboy\async\Message\Message\Message;
use zaboy\async\Message\Store as MessageStore;
use zaboy\async\ClientAbstract;
use zaboy\async\Queue\Interfaces\ClientInterface;
use Zend\Db\Sql\Select;
use zaboy\async\Message\Client as MessageClient;
use Zend\Db\Sql;
use zaboy\async\Promise\Client as PromiseClient;
use zaboy\async\Promise\Store as PromiseStore;

/**
 * Queue
 *
 * @category   async
 * @package    zaboy
 */
class Client extends ClientAbstract// implements ClientInterface
{

    const EXCEPTION_CLASS = '\zaboy\async\Queue\QueueException';

    /**
     *
     * @var MessageStore;
     */
    protected $messagesStore;

    /**
     *
     * @var PromiseStore;
     */
    protected $promisesStore;

    public function __construct(Store $store, $data = null)
    {
        parent::__construct($store, $data);
        $this->messagesStore = $this->getStore()->getMessagesStore();
        $this->promisesStore = $this->getStore()->getPromisesStore();
    }

    /**
     * @param string $priority
     *
     * @return bool
     */
    public function isEmpty()
    {
        return $this->getNumberMessages() === 0;
    }

    /**
     * @param string $priority
     *
     * @return int
     */
    public function getNumberMessages($priority = null)
    {
        if (is_null($priority)) {
            return $this->messagesStore->count([MessageStore::QUEUE_ID => $this->getId()]);
        } else {
            return $this->messagesStore->count([MessageStore::QUEUE_ID => $this->getId(), MessageStore::PRIORITY => $priority]);
        }
    }

    /**
     * @param string $priority
     *
     */
    public function purge($priority = null)
    {
        $priorityWhere = is_null($priority) ?
                [MessageStore::QUEUE_ID => $this->getId(),] :
                [MessageStore::QUEUE_ID => $this->getId(), MessageStore::PRIORITY => $priority];
        return $this->messagesStore->delete($priorityWhere);
    }

    /**
     * @param string $priority
     *
     */
    public function pullMessage($priority = null)
    {
        try {
            $errorMsg = "Can't start transaction for the method pullMessage";
            $this->store->beginTransaction();
            //is row with this index exist?
            $errorMsg = "Can't execute the method pullMessage";
            $db = $this->messagesStore->getAdapter();
            $sql = new Sql\Sql($db);
            $priorityWhere = is_null($priority) ?
                    [MessageStore::QUEUE_ID => $this->getId(),] :
                    [MessageStore::QUEUE_ID => $this->getId(), MessageStore::PRIORITY => $priority];
            $select = $sql->select()
                    ->from($this->messagesStore->getTable())
                    ->order([MessageStore::PRIORITY . ' DESC', MessageStore::CREATION_TIME])
                    ->where($priorityWhere)
                    ->limit(1, 0);

            $statement = $sql->prepareStatementForSqlObject($select);
            $rowset = $statement->execute();
            $data = $rowset->current();
            if (false === $data) {
                $this->messagesStore->commit();
                return null;
            }
            $messageId = $data[MessageStore::ID];
            $message = new MessageClient($this, $messageId);
            $messageBody = $message->getBody();
            $promise = $message->getPromise();
            $this->deleteMessage($message->getId());
            $promise->resolve($messageBody);
            $this->messagesStore->commit();
            return $messageBody;
        } catch (\Exception $e) {
            $this->messagesStore->rollback();
            throw new QueueException($errorMsg . 'Queue : ' . $this->getName(), 0, $e);
        }
    }

    public function getName()
    {
        return $this->getStoredData()[Store::NAME];
    }

    public function rename($name)
    {
        $id = $this->runTransaction('setName', $name);
        return $id;
    }

    protected function makeEntity($data = null)
    {
        $entity = new Queue($data);
        try {
            $data = $entity->getData();
            $rowsCount = $this->getStore()->insert($data);
        } catch (\Exception $e) {
            throw new QueueException('Can\'t insert data. Id: ' . $entity->getId(), 0, $e);
        }
        if (!$rowsCount) {
            throw new QueueException('Can\'t insert data. Id: ' . $entity->getId());
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
        return Queue::class;
    }

    public function toArray()
    {
        return $this->getStoredData();
    }

    /**
     *
     * @param type $body
     * @param type $priority
     */
    public function addMessage($body, $priority = null)
    {
        $priorityWhere = $priority ?
                [MessageStore::QUEUE_ID => $this->getId(), MessageStore::PRIORITY => $priority] :
                [MessageStore::QUEUE_ID => $this->getId(),];
        $messge = new MessageClient($this, [MessageStore::MESSAGE_BODY => $body, MessageStore::PRIORITY => $priority]);
        return $messge;
    }

    /**
     *
     * @param string $messageId
     */
    public function deleteMessage($messageId)
    {
        $message = new MessageClient($this, $messageId);
        return $message->remove();
    }

}
