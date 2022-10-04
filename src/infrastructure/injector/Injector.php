<?php

namespace App\infrastructure\injector;

use App\application\UserService;
use App\controller\HomeController;
use App\infrastructure\router\Router;
use DI\Container;
use DI\ContainerBuilder;
use DI\DependencyException;
use DI\NotFoundException;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use function DI\create;

class Injector {

    private Container $container;

    public function __construct() {
        $this->container = ContainerBuilder::buildDevContainer();
    }

    public function build(): void {

        $twig = new Environment(new FilesystemLoader(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR));

        $this->container->set(UserService::class, create(UserService::class));
        $this->container->set(Environment::class, $twig);
    }

    /**
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function setUpRouter(Router $router): void {

        $router->get('/', $this->container->get(HomeController::class), 'home');

    }

}