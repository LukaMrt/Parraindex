<?php

use App\infrastructure\injector\Injector;
use App\infrastructure\router\Router;
use DI\DependencyException;
use DI\NotFoundException;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

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

try {
	$injector->setUpRouter();
} catch (DependencyException|NotFoundException $e) {
	if ($_ENV['DEBUG'] === "true") {
		dd($e);
	}
}

$router->run();
