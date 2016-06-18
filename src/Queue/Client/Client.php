<?php

namespace zaboy\async\Queue\Client;

use ReputationVIP\QueueClient\QueueClient;
use zaboy\rest\DataStore\Interfaces\ReadInterface;
use zaboy\rest\DataStore\Interfaces\DataStoresInterface;

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
 */
class Client extends QueueClient //implements DataStoresInterface
{

    const MESSAGE_ID = ReadInterface::DEF_ID;
    const BODY = 'Body';
    const PRIORITY = 'priority';
    const TIME_IN_FLIGHT = 'time-in-flight';

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
    }

// ** Interface "zaboy\rest\DataStore\Interfaces\DataStoresInterface"  **/

    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}
     */
    public function create($itemData, $rewriteIfExist = false)
    {
        $this->addMessage($itemData[self::MESSAGE_ID], $message['Body'], $message['priority']);

        return $this->items[$id];
    }

    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}
     */
    public function update($itemData, $createIfAbsent = false)
    {
        $identifier = $this->getIdentifier();
        if (!isset($itemData[$identifier])) {
            throw new DataStoreException('Item must has primary key');
        }
        $id = $itemData[$identifier];
        $this->checkIdentifierType($id);

        switch (true) {
            case!isset($this->items[$id]) && !$createIfAbsent:
                $errorMsg = 'Can\'t update item with "id" = ' . $id;
                throw new DataStoreException($errorMsg);
            case!isset($this->items[$id]) && $createIfAbsent:
                $this->items[$id] = array_merge(array($identifier => $id), $itemData);
                break;
            case isset($this->items[$id]):
                unset($itemData[$id]);
                $this->items[$id] = array_merge($this->items[$id], $itemData);
                break;
        }
        return $this->items[$id];
    }

    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}
     */
    public function delete($id)
    {

        $this->checkIdentifierType($id);
        if (isset($this->items[$id])) {
            $item = $this->items[$id];
            unset($this->items[$id]);
        }
        return isset($item) ? $item : null;
    }

    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}
     */
    public function deleteAll()
    {
        $deletedItemsCount = count($this->items);
        $this->items = array();
        return $deletedItemsCount;
    }

// ** Interface "/Coutable"  **/

    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->items);
    }

// ** Interface "/IteratorAggregate"  **/

    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->items);
    }

// ** protected  **/

    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}
     */
    protected function getKeys()
    {
        return array_keys($this->items);
    }

}
