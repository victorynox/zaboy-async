<?php

namespace zaboy\async\Queue\Client;

use ReputationVIP\QueueClient\QueueClient;
use zaboy\rest\DataStore\Interfaces\ReadInterface;
use zaboy\rest\DataStore\Interfaces\DataStoresInterface;
use zaboy\async\Queue\QueueException;
use zaboy\async\Queue\Adapter\DataStoresAbstruct;
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
class Client extends QueueClient implements DataStoresInterface
{

    const MESSAGE_ID = ReadInterface::DEF_ID;
    const BODY = 'Body';
    const PRIORITY = 'priority';
    const TIME_IN_FLIGHT = 'time-in-flight';

    /**
     * Return adapter
     *
     * I have no idea why, but ReputationVIP\QueueClient\QueueClient
     * have not method getAdapter(). We fix it/
     *
     * @see ReputationVIP\QueueClient\QueueClient
     * @return \zaboy\async\Queue\Adapter\DataStoresAbstruct
     */
    public function getAdapter()
    {
        $reflection = new \ReflectionClass('\ReputationVIP\QueueClient\QueueClient');
        $adapterProperty = $reflection->getProperty('adapter');
        $adapterProperty->setAccessible(true);
        $adapter = $adapterProperty->getValue($this);
        $adapterProperty->setAccessible(false);
        return $adapter;
    }

    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}
     */
    public function read($id)
    {
        $queueName = $id;
        $messages = $this->getMessages($queueName, 1);
        $message = empty($messages) ? null : $messages[0];
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
        $queueName = $itemData[self::MESSAGE_ID];
        unset($itemData[self::MESSAGE_ID]);
        $message = $itemData;
        if (!isset($message['Body'])) {
            throw new QueueException('There is not "Body" key in message');
        }
        $priority = key_exists('priority', $message) ? $message['priority'] : null;
        $this->addMessage($queueName, $message['Body'], $priority);

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
