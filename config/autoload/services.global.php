<?php

use zaboy\async\Promise;

return [

    'services' => [
        'invokables' => [
        ],
        'factories' => [
            'TableManagerMysql' => 'zaboy\rest\TableGateway\Factory\TableManagerMysqlFactory',
            'QueueBroker' => 'zaboy\async\Queue\Factory\QueueBrokerFactory',
            Promise\Factory\StoreFactory::KEY => Promise\Factory\StoreFactory::class,
            Promise\Factory\BrokerFactory::KEY => Promise\Factory\BrokerFactory::class,
            Promise\Factory\CrudMiddlewareFactory::KEY => Promise\Factory\CrudMiddlewareFactory::class
        ],
        'abstract_factories' => [
            'zaboy\rest\DataStore\Factory\DbTableAbstractFactory',
            'zaboy\rest\DataStore\Factory\CsvAbstractFactory',
            'zaboy\rest\DataStore\Factory\MemoryAbstractFactory',
            'Zend\Db\Adapter\AdapterAbstractServiceFactory',
            'zaboy\async\Queue\Factory\QueueClientAbstractFactory',
            'zaboy\async\Queue\Factory\QueueAdapterAbstractFactory',
        //'zaboy\scheduler\Callback\Factory\InstanceAbstractFactory',
        ]
    ]
];
