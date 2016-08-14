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
use zaboy\async\Message;
use zaboy\async\ClientAbstract;
use zaboy\async\Queue\Interfaces\ClientInterface;
use Zend\Db\Sql\Select;

/**
 * Queue
 *
 * @category   async
 * @package    zaboy
 */
class Client extends ClientAbstract// implements ClientInterface
{

    /**
     *
     * @var Message\Store;
     */
    protected $messagesStore;

    public function __construct(Store $store, $data = null)
    {
        parent::__construct($store, $data);
        $this->messagesStore = $this->getStore()->getMessagesStore();
    }

    /**
     * @param string $priority
     *
     * @return bool
     */
    public function isEmpty($priority = null)
    {
        return 0;
    }

    /**
     * @param string $priority
     *
     * @return int
     */
    public function getNumberMessages($priority = null)
    {
        return 0;
    }

    /**
     * @param string $targetQueueName
     *
     */
    public function rename($name)
    {
        return 0;
    }

    /**
     * @param string $priority
     *
     */
    public function purge($priority = null)
    {
        $msgStore = $this->messagesStore;
        $msgStore->delete();
    }

    /**
     * @param string $priority
     *
     */
    public function pullMessage($priority = null)
    {

    }

    const EXCEPTION_CLASS = '\zaboy\async\Queue\QueueException';

    public function getName()
    {
        return $this->getStoredData()[Store::NAME];
    }

    public function setName($name)
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

}
