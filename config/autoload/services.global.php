<?php

return [

    'services' => [
        'invokables' => [
        ],
        'factories' => [
            'TableManagerMysql' => 'zaboy\rest\TableGateway\Factory\TableManagerMysqlFactory',
            'QueueBroker' => 'zaboy\async\Queue\Factory\QueueBrokerFactory',
            'MySqlPromiseAdapter' => 'zaboy\async\Promise\Factory\Adapter\MySqlAdapterFactory',
            'PromiseBroker' => 'zaboy\async\Promise\Factory\Broker\PromiseBrokerFactory'
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
