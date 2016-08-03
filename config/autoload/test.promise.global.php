<?php

use zaboy\async\Promise;
use zaboy\rest\DataStore\Factory;

global $testCase;

return [
    'services' => [
        'abstract_factories' => [
            Factory\HttpClientAbstractFactory::class,
        ],
        'factories' => [
            Promise\Factory\StoreFactory::KEY => Promise\Factory\StoreFactory::class,
            Promise\Factory\BrokerFactory::KEY => Promise\Factory\BrokerFactory::class,
            Promise\Factory\CrudMiddlewareFactory::KEY => Promise\Factory\CrudMiddlewareFactory::class,
        ],
        'aliases' => [
            //this 'promise' is service name in url
            'crud-promise' => Promise\Factory\CrudMiddlewareFactory::KEY,
        ],
    ],
    //
    Promise\Factory\StoreFactory::KEY => [
        Promise\Factory\StoreFactory::KEY_TABLE_NAME => $testCase === 'table for test' ? Promise\Factory\StoreFactory::TABLE_NAME . '_test' : null
    ],
    //
    'dataStore' => [
        'test_crud_client' => [
            Factory\HttpClientAbstractFactory::KEY_CLASS => zaboy\rest\DataStore\HttpClient::class,
            Factory\HttpClientAbstractFactory::KEY_URL => 'http://zaboy-async.loc/api/rest/crud-promise/',
        ],
    ]
];
