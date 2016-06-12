<?php

return [
    'queues' => [
        'MainQueue' => [
            'class' => 'zaboy\async\Queue\Client',
            'queuesDataStore' => 'QueuesDataStoreDbTable',
            'messagesDataStore' => 'MessagesDataStoreMemory',
        ],
    ],
    'dataStore' => [
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
