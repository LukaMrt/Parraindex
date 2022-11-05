<?php

namespace App\infrastructure\injector;

use App\application\person\PersonDAO;
use App\controller\ErrorController;
use App\controller\HomeController;
use App\controller\TreeController;
use App\controller\UpdateController;
use App\infrastructure\database\DatabaseConnection;
use App\infrastructure\person\MySqlPersonDAO;
use App\infrastructure\router\Router;
use DI\Container;
use DI\ContainerBuilder;
use DI\DependencyException;
use DI\NotFoundException;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;
use function DI\get;

class Injector {

	private Container $container;

	public function __construct() {
		$this->container = ContainerBuilder::buildDevContainer();
	}

	public function build(): void {

		$twig = $this->buildTwig();
		$databaseConnection = new DatabaseConnection();
		$userDAO = get(MySqlPersonDAO::class);

		$this->container->set(Environment::class, $twig);
		$this->container->set(DatabaseConnection::class, $databaseConnection);

		$this->container->set(PersonDAO::class, $userDAO);
	}

	/**
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	public function setUpRouter(Router $router): void {

		$router->registerRoute('GET', '/', $this->container->get(HomeController::class), 'home');
		$router->registerRoute('GET', '/tree', $this->container->get(TreeController::class), 'tree');
		$router->registerRoute('GET', '/update', $this->container->get(UpdateController::class), 'tree');
		$router->registerRoute('GET', '/[i:error]', $this->container->get(ErrorController::class), 'error');
		$router->registerRoute('GET', '/[*]', $this->container->get(ErrorController::class), '404');
		
	}

	private function buildTwig(): Environment {
		$twig = new Environment(new FilesystemLoader(dirname(__FILE__, 4) . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR));

		$twig->addFunction(new TwigFunction('style', function (string $path) {
			return 'css/' . $path;
		}));
		$twig->addFunction(new TwigFunction('script', function (string $path) {
			return 'js/' . $path;
		}));

		$twig->addFunction(new TwigFunction('image', function (string $path) {
			return 'img/' . $path;
		}));
		$twig->addFunction(new TwigFunction('picture', function (string $path) {
			return 'img/pictures/' . $path;
		}));
		$twig->addFunction(new TwigFunction('icon', function (string $path) {
			return 'img/icons/' . $path;
		}));

		return $twig;
	}

}