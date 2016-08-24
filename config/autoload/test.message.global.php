<?php

use zaboy\async\Message;
use zaboy\rest\DataStore\Factory;

global $testCase;

return [
    'services' => [
        'abstract_factories' => [
            Factory\HttpClientAbstractFactory::class,
        ],
        'factories' => [
            Message\Factory\StoreFactory::KEY => Message\Factory\StoreFactory::class,
//            Message\Factory\BrokerFactory::KEY => Message\Factory\BrokerFactory::class,
//            Message\Factory\CrudMiddlewareFactory::KEY => Message\Factory\CrudMiddlewareFactory::class,
        ],
//        'aliases' => [
//            //this 'promise' is service name in url
//            'crud-promise' => Promise\Factory\CrudMiddlewareFactory::KEY,
//        ],
    ],
    //
    Message\Factory\StoreFactory::KEY => [
        Message\Factory\StoreFactory::KEY_TABLE_NAME => $testCase === 'table_for_test' ? Message\Factory\StoreFactory::TABLE_NAME . '_test' : null
    ],
        //
//    'dataStore' => [
//        'test_crud_client' => [
//            Factory\HttpClientAbstractFactory::KEY_CLASS => zaboy\rest\DataStore\HttpClient::class,
//            Factory\HttpClientAbstractFactory::KEY_URL => 'http://zaboy-async.loc/test/crud-promise/',
//        ],
//    ]
];
