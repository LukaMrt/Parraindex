<?php

use App\infrastructure\injector\Injector;
use App\infrastructure\router\Router;
use DI\DependencyException;
use DI\NotFoundException;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor/autoload.php';

const DEBUG = true;

if (DEBUG) {
    $whoops = new Run();
    $whoops->pushHandler(new PrettyPageHandler);
    $whoops->register();
}

$router = new Router();
$injector = new Injector();
$injector->build();

try {
    $injector->setUpRouter($router);
} catch (DependencyException|NotFoundException $e) {
    if (DEBUG) {
        dump($e);
    }
}

$router->run();
