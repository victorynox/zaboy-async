<?php

return [
    'queues' => [
        'queueInMemory' => [
            'class' => 'zaboy\async\Queue\Client',
            'maxTimeInFlight' => 2,
            'queuesDataStore' => 'QueuesMemoryDataStore',
            'messagesDataStore' => 'MessagesMemoryDataStore',
        ],
        'MainQueue' => [
            'class' => 'zaboy\async\Queue\Client',
            'maxTimeInFlight' => 2,
            'queuesDataStore' => 'QueuesDataStoreDbTable',
            'messagesDataStore' => 'MessagesDataStoreMemory',
        ],
    ],
    'dataStore' => [
        'QueuesMemoryDataStore' => [
            'class' => 'zaboy\rest\DataStore\Memory'
        ],
        'MessagesMemoryDataStore' => [
            'class' => 'zaboy\rest\DataStore\Memory',
        ],
        'QueuesDataStoreDbTable' => [
            'class' => 'zaboy\rest\DataStore\Memory'
        //'tableName' => 'test_queues_tablle'
        ],
        'MessagesDataStoreMemory' => [
            'class' => 'zaboy\rest\DataStore\Memory',
        //'tableName' => 'test_messages_tablle'
        ],
    ]
];
