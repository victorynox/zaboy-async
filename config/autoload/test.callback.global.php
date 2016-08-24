<?php

use zaboy\async\Callback as Callback;
use zaboy\test\async\Callback as TestCallback;

global $testCase;

return [
    'services' => [
        'factories' => [
            TestCallback\Example\Factory\CallableServiceFactory::KEY => TestCallback\Example\Factory\CallableServiceFactory::class,
            Callback\Interrupter\Factory\ViaHttpMiddlewareFactory::KEY => Callback\Interrupter\Factory\ViaHttpMiddlewareFactory::class,
        ],
        'aliases' => [
            //this 'callback' is service name in url
            'callback-interrupter' => Callback\Interrupter\Factory\ViaHttpMiddlewareFactory::KEY,
        ],
    ],
];
