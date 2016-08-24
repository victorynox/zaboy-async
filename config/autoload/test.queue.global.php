<?php

use zaboy\async\Queue;
use zaboy\rest\DataStore\Factory;

global $testCase;

return [
    'services' => [
        'abstract_factories' => [
            Factory\HttpClientAbstractFactory::class,
        ],
        'factories' => [
            Queue\Factory\StoreFactory::KEY => Queue\Factory\StoreFactory::class,
//            Queue\Factory\BrokerFactory::KEY => Queue\Factory\BrokerFactory::class,
//            Queue\Factory\CrudMiddlewareFactory::KEY => Queue\Factory\CrudMiddlewareFactory::class,
        ],
//        'aliases' => [
//            //this 'promise' is service name in url
//            'crud-promise' => Promise\Factory\CrudMiddlewareFactory::KEY,
//        ],
    ],
    //
    Queue\Factory\StoreFactory::KEY => [
        Queue\Factory\StoreFactory::KEY_TABLE_NAME => $testCase === 'table_for_test' ? Queue\Factory\StoreFactory::TABLE_NAME . '_test' : null
    ],
        //
//    'dataStore' => [
//        'test_crud_client' => [
//            Factory\HttpClientAbstractFactory::KEY_CLASS => zaboy\rest\DataStore\HttpClient::class,
//            Factory\HttpClientAbstractFactory::KEY_URL => 'http://zaboy-async.loc/test/crud-promise/',
//        ],
//    ]
];
