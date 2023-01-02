<?php

use App\infrastructure\injector\Injector;
use App\infrastructure\router\Router;
use Whoops\Handler\PrettyPageHandler;

require_once('../vendor/autoload.php');

session_start();
$dotenv = Dotenv\Dotenv::createImmutable('../');
$dotenv->load();

if ($_ENV['DEBUG'] === "true") {
    (new Whoops\Run())->pushHandler(new PrettyPageHandler())->register();
}

$router = new Router();
$injector = new Injector($router);
$injector->build();
$injector->setUpRouter();

$start = microtime(true);

$router->run();

$end = microtime(true);

if ($_ENV['DEBUG'] === "true") {
    echo "Execution time: " . round(($end - $start) * 1_000) . " ms";
}
