<?php

use App\infrastructure\injector\Injector;
use App\infrastructure\router\Router;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';


session_start();
$dotenv = Dotenv\Dotenv::createImmutable('../');
$dotenv->load();

if ($_ENV['DEBUG'] === "true") {
	$whoops = new Run();
	$whoops->pushHandler(new PrettyPageHandler());
	$whoops->register();
}

$router = new Router();
$injector = new Injector($router);
$injector->build();
$injector->setUpRouter();

$start = microtime(true);

$router->run();

$end = microtime(true);

if ($_ENV['DEBUG'] === "true") {
	echo "Execution time: " . ($end - $start) . " seconds";
}
