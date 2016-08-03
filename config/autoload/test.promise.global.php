<?php

use zaboy\async\Promise;

global $testCase;

return [
    'services' => [
        'factories' => [
            Promise\Factory\StoreFactory::KEY => Promise\Factory\StoreFactory::class,
            Promise\Factory\BrokerFactory::KEY => Promise\Factory\BrokerFactory::class,
        ],
    ],
    Promise\Factory\StoreFactory::KEY => [
        Promise\Factory\StoreFactory::KEY_TABLE_NAME => $testCase === 'table for test' ? Promise\Factory\StoreFactory::TABLE_NAME . '_test' : null
    ]
];
