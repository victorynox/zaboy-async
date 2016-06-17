<?php

return [
    'queueAdapter' => [
        'Test-Mysql_OueueAdapter 2sec' => [
            'class' => 'zaboy\async\Queue\Adapter\MysqlOueueAdapter'
        ],
        'TestMemoryStoresOueueAdapter' => [
            'class' => 'zaboy\async\Queue\Adapter\MemoryStoresOueueAdapter'
        ],
        'TestDataStoresOueueAdapter' => [
            'class' => 'zaboy\async\Queue\Adapter\DataStores',
            //there are additional options in this case
            'QueuesDataStore' => 'QueuesMemoryDataStore',
            'MessagesDataStore' => 'MessagesMemoryDataStore'
        ]
    /*     * ,
      'MainQueue' => [
      'class' => 'zaboy\async\Queue\Client\Client',
      'maxTimeInFlight' => 2,
      'queuesDataStore' => 'QueuesDataStoreDbTable',
      'messagesDataStore' => 'MessagesDataStoreMemory',
      ], */
    ],
    'queueClient' => [
        'testMysqlQueue' => [
            'QueueAdapter' => 'Test-Mysql_OueueAdapter 2sec',
            'maxTimeInFlight' => 2
        ],
        'testDataStoresQueue' => [
            'QueueAdapter' => 'TestDataStoresOueueAdapter',
            'maxTimeInFlight' => 2
        ],
    ],
    'dataStore' => [
        'QueuesMemoryDataStore' => [
            'class' => 'zaboy\rest\DataStore\Memory'
        ],
        'MessagesMemoryDataStore' => [
            'class' => 'zaboy\rest\DataStore\Memory',
        ],
    ]
];
