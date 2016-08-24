<?php

// try http://__zaboy-rest/api/rest/index_StoreMiddleware?fNumberOfHours=8&fWeekday=Monday
// Change to the project root, to simplify resolving paths
chdir(dirname(__DIR__));
if (substr($_SERVER['REQUEST_URI'], 1, 4) === 'test') {
    global $testCase;
    $testCase = 'table_for_test';
}

//test_res_http
// Setup autoloading
require 'vendor/autoload.php';
$container = include 'config/container.php';

use zaboy\async\Callback\Callback;

Callback::setContaner($container);

//use zaboy\async\Json\JsonCoder;
////
//function callback($value)
//{
//    return $value . ' after callbak';
//}
//function callException($value)
//{
//    throw new \Exception('Exception ', 0, new \Exception('prev Exception'));
//}
//
//function onRejected($value)
//{
//    /* @var $value \Exception */
//    return $value->getMessage() . ' was resolved';
//}
//
//$mySqlAdapterFactory = new StoreFactory();
//
//$mySqlPromiseAdapter = $mySqlAdapterFactory->__invoke(
//        $container
//        , ''
//        , [StoreFactory::KEY_TABLE_NAME => 'test_mysqlpromisebroker']
//);
//
//
//$result = new Client($mySqlPromiseAdapter);
//$object = new Client($mySqlPromiseAdapter);
//
//$object->resolve($result);
//$result->resolve('result');
//
//$r = $object->wait(FALSE);
//
//
//var_dump($object->wait(FALSE));
//
//exit();

use zaboy\rest\Pipe\MiddlewarePipeOptions;
use Zend\Diactoros\Server;
use zaboy\rest\Pipe\Factory\RestRqlFactory;
use zaboy\rest\DataStore\HttpClient;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/* @var $httpClientQueue HttpClient */


//$container->get('QueueBroker');
//$queue = $container->get('testMysqlQueue');
//$queue->addMessage('ManagedQueue11', '$value[0]');
//$queuecreate([Client::MESSAGE_ID => 'ManagedQueue11', Client::BODY => 'test_create_delete()__1']);

putenv("APP_ENV=dev"); //putenv("APP_ENV=pro"); - it is for compose autoload config cache

$env = getenv('APP_ENV') === 'dev' ? 'develop' : null; // - it is for MiddlewarePipe debug information
$app = new MiddlewarePipeOptions(['env' => $env]); //'env' => 'develop' (error ifor show)) or 'env' => 'any another' (do not show))

$RestRqlFactory = new RestRqlFactory();
$rest = $RestRqlFactory($container, '');

$app->pipe('/api/rest', $rest);

$app->pipe('/test', $rest);





$server = Server::createServer($app, $_SERVER, $_GET, $_POST, $_COOKIE, $_FILES
);
$server->listen();
