<?php

return [

    'services' => [
        'invokables' => [
        ],
        'factories' => [
            'TableManagerMysql' => 'zaboy\rest\TableGateway\Factory\TableManagerMysqlFactory',
            'QueueBroker' => 'zaboy\async\Queue\Factory\QueueBrokerFactory',
            'MySqlPromiseAdapter' => 'zaboy\async\Promise\Factory\Adapter\MySqlAdapterFactory',
            'PromiseBroker' => 'zaboy\async\Promise\Factory\Broker\PromiseBrokerFactory',
            zaboy\async\Promise\Factory\Middleware\CrudPromiseFactory::KEY_MIDDLEWARE_CRUD_PROMISE => 'zaboy\async\Promise\Factory\Middleware\CrudPromiseFactory'
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
