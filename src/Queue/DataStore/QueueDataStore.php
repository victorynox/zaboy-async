<?php

namespace zaboy\async\Queue\DataStore;

use zaboy\rest\DataStore;
use zaboy\rest\DataStore\DataStoreAbstract;
use zaboy\async\Queue\Client\Client;
use zaboy\async\Queue\QueueException;
use Xiag\Rql\Parser\Query;
use zaboy\rest\DataStore\ConditionBuilder\RqlConditionBuilder;

/**
 * You can use queue as data store
 *
 * <code>
 *
 * //How to get meeage from queue:
 * $message = $this->read(null) or 'null' or 'null()'
 * You'ii get
 * [
 *     'id' => '1_ManagedQueue11__576522deb5ad08'
 *     'Body' => mix
 *     'priority' => 'HIGH'
 *     'time-in-flight' => 1466245854
 * ]
 *
 * Add to queue:
 * $message = [
 *     'id' => '1_ManagedQueue11__576522deb5ad08'
 *     'Body' => mix
 *     'priority' => 'HIGH'
 *     'time-in-flight' => 1466245854
 * ]
 * $this->create($message)
 *
 * Delete from queue:
 * $this->delete( '1_ManagedQueue11__576522deb5ad08')
 * or (better):
 * $this->delete(['id' => '1_ManagedQueue11__576522deb5ad08']) or
 *  </code>
 *
 * @category   async
 * @package    zaboy
 */
class QueueDataStore extends DataStore\DataStoreAbstract
{

    const TEXT_NULL = 'null';

    /**
     *
     * @var \zaboy\async\Queue\Client\Client
     */
    protected $queueClient;

    /**
     *
     * @var DataStoreAbstract
     */
    protected $messagesDataStore;

    /**
     *
     * @var string
     */
    protected $queueName;

    public function __construct(Client $queueClient, $queueName)
    {
        $this->queueClient = $queueClient;
        $this->queueName = $queueName;
        $this->messagesDataStore = $queueClient->getAdapter()->getMessagesDataStore();
    }

    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}
     */
    public function read($id)
    {
        if ($id === null || $id === RqlConditionBuilder::TEXT_NULL || self::TEXT_NULL) {
            $message = $this->queueClient->getMessages($this->queueName, 1);
            $message = !$message ? null : $message[0];
        } else {
            $message = $this->messagesDataStore->read($id);
        }
        return $message;
    }

// ** Interface "zaboy\rest\DataStore\Interfaces\DataStoresInterface"  **/

    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}
     */
    public function create($itemData, $rewriteIfExist = false)
    {
        if (!isset($itemData[Client::BODY])) {
            throw new QueueException('There is not "Body" key in message');
        }
        $priority = key_exists(Client::PRIORITY, $itemData) ? $itemData[Client::PRIORITY] : null;
        $this->queueClient->addMessage($this->queueName, $itemData[Client::BODY], $priority);

        return $itemData;
    }

    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}
     */
    public function delete($id)
    {
        if (!is_string($id) && !isset($id[Client::MESSAGE_ID])) {
            throw new QueueException('"id" must be string or array("id"->string)');
        }
        if (isset($id[Client::MESSAGE_ID])) {
            $idSting = $id[Client::MESSAGE_ID];

            if ((bool) strpos($idSting, $this->queueName)) {
                throw new QueueException('Yuo try to delete message ' . $idSting . 'from queue' . $this->queueName);
            }
            $this->queueClient->deleteMessage($this->queueName, $id);
        } else {
            $message = $this->messagesDataStore->delete($id);
        }
        return $message;
    }

    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}
     */
    public function update($itemData, $createIfAbsent = false)
    {
        $this->messagesDataStore->update($itemData);
    }

    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}
     */
    public function query(Query $query)
    {
        return $this->messagesDataStore->query($query);
    }

// ** Interface "/IteratorAggregate"  **/

    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return $this->messagesDataStore->getIterator();
    }

    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return Client::MESSAGE_ID;
    }

    public function getQueueClient()
    {
        return $this->queueClient;
    }

    /**
     * @param $methodName
     * @throws TimelineDataStoreException
     */
    protected function throwException($methodName)
    {
        throw new QueueException(
        'The DataStore type zaboy\async\Queue\Client\Client doesn\'t allow to work with method: ' . $methodName
        );
    }

}
