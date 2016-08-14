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
     * @var Message\Store;
     */
    protected $messagesStore;

    public function __construct(Queue\Client $queue, $data)
    {
        $store = $queue->getStore()->getMessagesStore();
        parent::__construct($store, $data);
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

}
