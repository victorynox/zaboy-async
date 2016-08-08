<?php

namespace zaboy\async\Queue\Factory\Adapter;

use Interop\Container\ContainerInterface;
use zaboy\rest\DataStore\DataStoreException;
use zaboy\rest\DataStore\DbTable;
use zaboy\rest\FactoryAbstract;
use Zend\Db\TableGateway\TableGateway;
use zaboy\rest\TableGateway\TableManagerMysql;
use zaboy\async\Queue\Adapter;
use zaboy\async\Queue\Adapter\DataStoresAbstract as QueueDataStores;
use zaboy\rest\DataStore\DataStoreAbstract as DataStore;

/**
 * Creates if can and returns an instance of class Queue\Adapter\DataStoresAbstract - Adapter for Queue
 *
 * Class ScriptAbstractFactory
 *
 * @category   async
 * @package    zaboy
 */
class MySqlAdapterFactory extends FactoryAbstract
{

    const QUEUES_TABLE_NAME_PREFIX = 'queue_queues_';
    const MESSAGES_TABLE_NAME_PREFIX = 'queue_messages_';
    const FIELD_TYPE = 'field_type';
    const FIELD_PARAMS = 'field_params';

    /** @var \Zend\Db\Adapter\Adapter $db */
    protected $db;

    /** @var  \zaboy\rest\DataStore\DbTable */
    protected $dataStore;
    protected $queuesTableData = [
        DataStore::DEF_ID => [
            'field_type' => 'Varchar',
            'field_params' => [
                'length' => 128,
                'nullable' => false
            ]
        ]
    ];
    protected $messagesTableData = [
        DataStore::DEF_ID => [
            'field_type' => 'Varchar',
            'field_params' => [
                'length' => 128,
                'nullable' => false
            ]
        ],
        QueueDataStores::QUEUE_NAME => [
            'field_type' => 'Varchar',
            'field_params' => [
                'length' => 128,
                'nullable' => false
            ]
        ],
        QueueDataStores::MESSAGE_BODY => [
            'field_type' => 'Varchar',
            'field_params' => [
                'length' => 65535,
                'nullable' => false
            ]
        ],
        QueueDataStores::PRIORITY => [
            'field_type' => 'Integer',
            'field_params' => [
                'nullable' => false
            ]
        ],
        QueueDataStores::TIME_IN_FLIGHT => [
            'field_type' => 'Integer',
            'field_params' => [
                'nullable' => false
            ]
        ],
        QueueDataStores::CREATED_ON => [
            'field_type' => 'Integer',
            'field_params' => [
                'nullable' => false
            ]
        ]
    ];

    /**
     * {@inherit}
     *
     * {@inherit}
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $this->db = $container->has('db') ? $container->get('db') : null;
        if (is_null($this->db)) {
            throw new DataStoreException(
            'Can\'t create DbTableAdapter for Queue'
            );
        }

        if ($container->has('TableManagerMysql')) {
            $tableManager = $container->get('TableManagerMysql');
        } else {
            $tableManager = new TableManagerMysql($this->db);
        }

        $tableNameQueues = self::QUEUES_TABLE_NAME_PREFIX . strtolower(str_replace(['-', '_', ' '], '', $requestedName));
        $hasTableQueues = $tableManager->hasTable($tableNameQueues);

        if (!$hasTableQueues) {
            $tableManager->rewriteTable($tableNameQueues, $this->queuesTableData);
        }
        $tableGatewayQueues = new TableGateway($tableNameQueues, $this->db);

        $queuesDataStore = new DbTable($tableGatewayQueues);

        $tableNameMessages = self::MESSAGES_TABLE_NAME_PREFIX . strtolower(str_replace(['-', '_', ' '], '', $requestedName));
        $hasTableMessages = $tableManager->hasTable($tableNameMessages);
        if (!$hasTableMessages) {
            $tableManager->rewriteTable($tableNameMessages, $this->messagesTableData);
        }
        $tableGatewayMessages = new TableGateway($tableNameMessages, $this->db);
        $messagesDataStore = new DbTable($tableGatewayMessages);

        $adapterQueues = new Adapter\MysqlQueueAdapter($queuesDataStore, $messagesDataStore);
        return $adapterQueues;
    }

}
