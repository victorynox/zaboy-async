<?php

return [
    'callback' => [
        'instance_worker_callback' => [
            'class' => 'zaboy\scheduler\Callback\Instance',
            'params' => [
                'instanceServiceName' => 'test_worker_datastore',
                'instanceMethodName' => 'create',
            ],
        ],
    ],
    'queueBroker' => [
        'testMysqlQueue' => [
            'ManagedQueue11' => [
                'workerName' => 'instance_worker_callback',
                'messagesNumberInQuery' => 10
            ],
            'ManagedQueue12' => [
                'workerName' => 'instance_worker_callback'
            ]
        ],
        'testDataStoresQueue' => [
            'ManagedQueue21' => [
                'workerName' => 'instance_worker_callback'
            ],
            'ManagedQueue22' => [
                'workerName' => 'instance_worker_callback'
            ]
        ],
    ],
    'queueAdapter' => [
        'Test-Mysql_QueueAdapter 2sec' => [
            'class' => 'zaboy\async\Queue\Adapter\MysqlQueueAdapter'
        ],
        'TestMemoryStoresQueueAdapter' => [
            'class' => 'zaboy\async\Queue\Adapter\MemoryStoresQueueAdapter'
        ],
        'TestDataStoresQueueAdapter' => [
            'class' => 'zaboy\async\Queue\Adapter\DataStores',
            //there are additional options in this case
            'QueuesDataStore' => 'QueuesMemoryDataStore',
            'MessagesDataStore' => 'MessagesMemoryDataStore'
        ]
    ],
    'queueClient' => [
        'testMysqlQueue' => [
            'QueueAdapter' => 'Test-Mysql_QueueAdapter 2sec',
            'maxTimeInFlight' => 2
        ],
        'testDataStoresQueue' => [
            'QueueAdapter' => 'TestDataStoresQueueAdapter',
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
        'test_worker_datastore' => [
            'class' => 'zaboy\rest\DataStore\Memory',
        ],
    ]
];
