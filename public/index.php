<?php

chdir(dirname(__DIR__));
define('REQUEST_MICROTIME', microtime(true));
define('DEV_MOVE', false);

if (DEV_MOVE) {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
}
// Setup autoloading
require './vendor/autoload.php';



use Zend\Stratigility\MiddlewarePipe;
use Zend\Diactoros\Server;

require __DIR__ . '/../vendor/autoload.php';

$app    = new MiddlewarePipe();
$server = Server::createServer($app, $_SERVER, $_GET, $_POST, $_COOKIE, $_FILES);

$appConfig = include './config/application.config.php';
if (file_exists('./config/development.config.php') && !DEV_MOVE) {
    $appConfig = array_merge($appConfig, include APPLICATION_PATH . '/config/development.config.php');
}

$app->pipe(new Opro\Middleware\ZendFramework2($appConfig));


$server->listen();

