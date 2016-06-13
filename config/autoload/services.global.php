<?php

return [

    'services' => [
        'invokables' => [
        ],
        'factories' => [
            'defaultQueueAdapter' => 'zaboy\async\Queue\Factory\MySqlAdapterFactory',
            'defaultQueueClient' => 'zaboy\async\Queue\Factory\QueueClientFactory'
        ],
        'abstract_factories' => [
            'zaboy\rest\DataStore\Factory\DbTableAbstractFactory',
            'zaboy\rest\DataStore\Factory\CsvAbstractFactory',
            'zaboy\rest\DataStore\Factory\MemoryAbstractFactory',
            'Zend\Db\Adapter\AdapterAbstractServiceFactory',
            'zaboy\async\Queue\Factory\QueueClientAbstracFactory',
        ]
    ]
];
