<?php

namespace zaboy\async\Queue\DataStore;

use zaboy\rest\DataStore;
use zaboy\async\Queue\Client\Client;
use zaboy\async\Queue\QueueException;
use Xiag\Rql\Parser\Query;

/**
 *
 * <code>
 * $message = [
 *     'id' => '1_ManagedQueue11__576522deb5ad08'
 *     'Body' => mix
 *     'priority' => 'HIGH'
 *     'time-in-flight' => 1466245854
 * ]
 *  </code>
 *
 * @category   async
 * @package    zaboy
 */
class QueueDataStore extends DataStore\DataStoreAbstract
{

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

    public function __construct(Client $queueClient)
    {
        $this->queueClient = $queueClient;
        $this->messagesDataStore = $queueClient->getAdapter()->getMessagesDataStore();
    }

    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}
     */
    public function read($id)
    {
        return $this->messagesDataStore->read($id);
    }

// ** Interface "zaboy\rest\DataStore\Interfaces\DataStoresInterface"  **/

    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}
     */
    public function create($itemData, $rewriteIfExist = false)
    {
        $queueName = $itemData[self::QUEUE];
        unset($itemData[self::QUEUE]);
        $message = $itemData;
        if (!isset($message[self::BODY])) {
            throw new QueueException('There is not "Body" key in message');
        }
        $priority = key_exists(self::PRIORITY, $message) ? $message[self::PRIORITY] : null;
        $this->addMessage($queueName, $message[self::BODY], $priority);

        return $message;
    }

    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}
     */
    public function delete($id)
    {
        $queueName = explode(DataStoresAbstruct::ID_SEPARATOR, $id)[1];
        $message = [self::MESSAGE_ID => $id];
        $this->deleteMessage($queueName, $message);
        return $message;
    }

    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}
     */
    public function query(Query $query)
    {
        return['zaboy\async\Queue\Client\Client doesn\'t allow to work with method: query'];
    }

    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}
     */
    public function has($id)
    {
        $this->throwException('has');
    }

    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}
     */
    public function update($itemData, $createIfAbsent = false)
    {
        $this->throwException('update');
    }

    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}
     */
    public function deleteAll()
    {
        $this->throwException('deleteAll');
    }

// ** Interface "/Coutable"  **/

    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}
     */
    public function count()
    {
        $this->throwException('count');
    }

// ** Interface "/IteratorAggregate"  **/

    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}
     */
    public function getIterator()
    {
        $this->throwException('getIterator');
    }

// ** protected  **/

    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}
     */
    protected function getKeys()
    {
        $this->throwException('getKeys');
    }

    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return self::MESSAGE_ID;
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
