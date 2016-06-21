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
class Client extends QueueClient
{

    const MESSAGE_ID = ReadInterface::DEF_ID;
    const BODY = 'Body';
    const PRIORITY = 'priority';
    const TIME_IN_FLIGHT = 'time-in-flight';

    /**
     * @param AdapterInterface $adapter
     */
    public function __construct(DataStoresAbstruct $adapter)
    {
        parent::__construct($adapter);
    }

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

}
