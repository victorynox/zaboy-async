<?php

namespace zaboy\async\Queue\Factory;

use Interop\Container\ContainerInterface;
use zaboy\rest\DataStore\DataStoreException;
use zaboy\rest\DataStore\DbTable;
use zaboy\rest\FactoryAbstract;
use Zend\Db\TableGateway\TableGateway;
use zaboy\rest\TableGateway\TableManagerMysql;
use zaboy\async\Queue\Adapter;
use zaboy\async\Queue\Adapter\DataStoresAbstruct as QueueDataStores;
use zaboy\rest\DataStore\DataStoreAbstract as DataStore;

/**
 * Creates if can and returns an instance of class Queue\Adapter\DataStoresAbstruct - Adapter for Queue
 *
 * Class ScriptAbstractFactory
 * @package zaboy\scheduler\Callback\Factory
 */
class MySqlAdapterFactory extends FactoryAbstract
{

    const QUEUES_TABLE_NAME = 'queues_main';
    const MESSAGES_TABLE_NAME = 'messages_main';
    const FILD_TYPE = 'fild_type';
    const FILD_PARAMS = 'fild_params';

    /** @var \Zend\Db\Adapter\Adapter $db */
    protected $db;

    /** @var  \zaboy\rest\DataStore\DbTable */
    protected $dataStore;
    protected $queuesTableData = [
        DataStore::DEF_ID => [
            'fild_type' => 'Varchar',
            'fild_params' => [
                'length' => 32,
                'nullable' => false
            ]
        ]
    ];
    protected $messagesTableData = [
        DataStore::DEF_ID => [
            'fild_type' => 'Integer',
            'fild_params' => [
                'options' => ['autoincrement' => true]
            ]
        ],
        QueueDataStores::QUEUE_NAME => [
            'fild_type' => 'Varchar',
            'fild_params' => [
                'length' => 32,
                'nullable' => false
            ]
        ],
        QueueDataStores::MESSAGE_BODY => [
            'fild_type' => 'Varchar',
            'fild_params' => [
                'length' => 65535,
                'nullable' => false
            ]
        ],
        QueueDataStores::PRIORITY => [
            'fild_type' => 'Integer',
            'fild_params' => [
                'nullable' => false
            ]
        ],
        QueueDataStores::TIME_IN_FLIGHT => [
            'fild_type' => 'Integer',
            'fild_params' => [
                'nullable' => false
            ]
        ],
        QueueDataStores::CREATED_ON => [
            'fild_type' => 'Integer',
            'fild_params' => [
                'nullable' => false
            ]
        ]
    ];

    /**
     * {@inherit}
     *
     * {@inherit}
     */
    public function __invoke(ContainerInterface $container)
    {
        $this->db = $container->has('db') ? $container->get('db') : null;
        if (is_null($this->db)) {
            throw new DataStoreException(
            'Can\'t create DbTableAdapter for Queue'
            );
        }

        $tableManager = new TableManagerMysql($this->db, self::QUEUES_TABLE_NAME);
        $hasTable = $tableManager->hasTable();
        if (!$hasTable) {
            $tableManager->rewriteTable($this->queuesTableData);
        }
        $tableGateway = new TableGateway(self::QUEUES_TABLE_NAME, $this->db);
        $queuesDataStore = new DbTable($tableGateway);



        $tableManager = new TableManagerMysql($this->db, self::MESSAGES_TABLE_NAME);
        $hasTable = $tableManager->hasTable();
        if (!$hasTable) {
            $tableManager->rewriteTable($this->messagesTableData);
        }
        $tableGateway = new TableGateway(self::MESSAGES_TABLE_NAME, $this->db);
        $messagesDataStore = new DbTable($tableGateway);

        $adapterQueues = new Adapter\DataStores($queuesDataStore, $messagesDataStore);

        return $adapterQueues;
    }

}
