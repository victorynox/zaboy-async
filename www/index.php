<?php

// try http://__zaboy-rest/api/rest/index_StoreMiddleware?fNumberOfHours=8&fWeekday=Monday
// Change to the project root, to simplify resolving paths
chdir(dirname(__DIR__));
//test_res_http
// Setup autoloading
require 'vendor/autoload.php';
$container = include 'config/container.php';

use zaboy\async\Promise\PromiseClient;
use zaboy\async\Promise\Factory\Adapter\MySqlAdapterFactory;

$mySqlAdapterFactory = new MySqlAdapterFactory();
$mySqlPromiseAdapter = $mySqlAdapterFactory->__invoke(
        $container
        , ''
        , [MySqlAdapterFactory::KEY_PROMISE_TABLE_NAME => 'test_mysqlpromisebroker']
);

$object = new PromiseClient($mySqlPromiseAdapter);
$object->reject(new \stdClass());
$object->wait(false);
var_dump(get_class($object->wait(false)));

use zaboy\rest\Pipe\MiddlewarePipeOptions;
use Zend\Diactoros\Server;
use zaboy\rest\Pipe\Factory\RestRqlFactory;
use zaboy\rest\DataStore\HttpClient;

/* @var $httpClientQueue HttpClient */


$container->get('QueueBroker');
//$queue = $container->get('testMysqlQueue');
//$queue->addMessage('ManagedQueue11', '$value[0]');
//$queuecreate([Client::MESSAGE_ID => 'ManagedQueue11', Client::BODY => 'test_create_delete()__1']);

$app = new MiddlewarePipeOptions([]); //'env' => 'develop'
$RestRqlFactory = new RestRqlFactory();
$rest = $RestRqlFactory($container, '');
$app->pipe('/api/queue', $rest);

$server = Server::createServer($app, $_SERVER, $_GET, $_POST, $_COOKIE, $_FILES
);
$server->listen();
