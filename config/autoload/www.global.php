<?php

return [
    'services' => [
        'abstract_factories' => [
            'zaboy\rest\DataStore\Factory\HttpClientAbstractFactory',
            'zaboy\async\Queue\Factory\DataStore\ClientDataStoreAbstractFactory'
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
        ]
    ]
];
