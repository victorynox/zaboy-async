<?php

use zaboy\test\async\Callback;

global $testCase;
//var_dump($testCase);
return [
    'services' => [
        'factories' => [
            Callback\Example\Factory\CallableServiceFactory::KEY => Callback\Example\Factory\CallableServiceFactory::class,
        ],
    ],
];
