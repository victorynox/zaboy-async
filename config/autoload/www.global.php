<?php

return [
    'services' => [
        'abstract_factories' => [
            'zaboy\rest\DataStore\Factory\HttpClientAbstractFactory',
            'zaboy\async\Queue\Factory\DataStore\ClientDataStoreAbstractFactory',
            'zaboy\async\Queue\Factory\DataStore\QueueDataStoreAbstractFactory'
        ]
    ],
    'dataStore' => [
        'HttpClientQueue' => [
            'class' => 'zaboy\rest\DataStore\HttpClient',
            'url' => 'http://zaboy-async.loc/api/queue/testMysqlQueue',
            'options' => ['timeout' => 30]
        ],
        'test_ClientDataStore' => [
            'queueClient' => 'testMysqlQueue'
        ],
        'test_QueueDataStore' => [
            'queueClient' => 'testMysqlQueue', //name of service
            'queueName' => 'ManagedQueue11'   //name of queue (not service name
        ]
    ]
];
