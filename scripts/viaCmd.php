<?php

/**
 * Created by PhpStorm.
 * User: root
 * Date: 25.08.16
 * Time: 17:18
 */

global $testCase;
$testCase = 'table_for_test';

$path = getcwd();
if (!is_file($path . '/vendor/autoload.php')) {
    $path = dirname(getcwd());
}
chdir($path);
require $path . '/vendor/autoload.php';
$container = include './config/container.php';

use zaboy\async\Promise\Client as PromiseClient;
use zaboy\async\Promise\Factory\StoreFactory;

if ($argc > 1) {

    $data = unserialize(base64_decode($argv[1]));
    extract($data);
    /** @var $promiseId  */
    /** @var $value  */
    /** @var callable $callback */

    $store = $container->get(StoreFactory::KEY);
    $promise = new PromiseClient($store, $promiseId);
    $callback($value, $promise);
}