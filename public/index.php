<?php

use App\application\UserService;
use App\controller\HomeController;
use App\infrastructure\router\Router;
use DI\ContainerBuilder;
use DI\DependencyException;
use DI\NotFoundException;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;
use function DI\create;

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor/autoload.php';

const DEBUG = true;

if (DEBUG) {
    $whoops = new Run();
    $whoops->pushHandler(new PrettyPageHandler);
    $whoops->register();
}

$twig = new Environment(new FilesystemLoader(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR));

$container = ContainerBuilder::buildDevContainer();
$container->set(UserService::class, create(UserService::class));
$container->set(Environment::class, $twig);

try {
    (new Router())
        ->get('/', $container->get(HomeController::class), 'home')
        ->run();
} catch (DependencyException|NotFoundException $e) {
    if (DEBUG) {
        dump($e);
    }
}
