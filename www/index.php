<?php

// try http://__zaboy-rest/api/rest/index_StoreMiddleware?fNumberOfHours=8&fWeekday=Monday
// Change to the project root, to simplify resolving paths
chdir(dirname(__DIR__));
//test_res_http
// Setup autoloading
require 'vendor/autoload.php';

use GuzzleHttp\Promise\Promise;

$p1 = new Promise(null, function () {
    echo 'cancel p1';
});

$p21 = $p1->then(function ($res) {
    echo $res . ' + resalt p21' . PHP_EOL . '<br>';
});

$p22 = $p1->then(function ($res) {
    echo $res . ' + resalt p22' . PHP_EOL . '<br>';
});

$p21->cancel();
$p1->resolve('resalt1');

//$p1->cancel();
//var_dump($p1);
exit;

use zaboy\rest\Pipe\MiddlewarePipeOptions;
use Zend\Diactoros\Server;
use zaboy\rest\Pipe\Factory\RestRqlFactory;
use zaboy\rest\DataStore\HttpClient;

/* @var $httpClientQueue HttpClient */
$container = include 'config/container.php';

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
