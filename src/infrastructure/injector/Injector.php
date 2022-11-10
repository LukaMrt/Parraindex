<?php

namespace App\infrastructure\injector;

use App\application\login\AccountDAO;
use App\application\login\SessionManager;
use App\application\contact\ContactDAO;
use App\application\person\PersonDAO;
use App\application\redirect\Redirect;
use App\controller\ContactController;
use App\controller\ErrorController;
use App\controller\HomeController;
use App\controller\LoginController;
use App\controller\SignUpController;
use App\controller\ResetpasswordController;
use App\controller\TreeController;
use App\controller\UpdateController;
use App\infrastructure\accountService\MysqlAccountDAO;
use App\infrastructure\database\contact\MysqlContactDAO;
use App\infrastructure\database\DatabaseConnection;
use App\infrastructure\person\MySqlPersonDAO;
use App\infrastructure\redirect\HttpRedirect;
use App\infrastructure\router\Router;
use App\infrastructure\session\DefaultSessionManager;
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
	private Router $router;

	public function __construct(Router $router) {
		$this->container = ContainerBuilder::buildDevContainer();
		$this->router = $router;
	}

	public function build(): void {

		$twig = $this->buildTwig();
		$databaseConnection = new DatabaseConnection();
		$userDAO = get(MySqlPersonDAO::class);
        $accountDAO = get(MySqlAccountDAO::class);
        $sessionManager = get(DefaultSessionManager::class);
		$redirect = get(HttpRedirect::class);
		$personDAO = get(MySqlPersonDAO::class);
		$contactDAO = get(MySqlContactDAO::class);

        $this->container->set(Environment::class, $twig);
		$this->container->set(DatabaseConnection::class, $databaseConnection);
        $this->container->set(Router::class, $this->router);
		$this->container->set(Router::class, $this->router);
		$this->container->set(Redirect::class, $redirect);

        $this->container->set(SessionManager::class, $sessionManager);

		$this->container->set(PersonDAO::class, $userDAO);
        $this->container->set(AccountDAO::class, $accountDAO);
		$this->container->set(PersonDAO::class, $personDAO);
		$this->container->set(ContactDAO::class, $contactDAO);
	}

	/**
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	public function setUpRouter(): void {
		$this->router->registerRoute('GET', '/', $this->container->get(HomeController::class), 'home');
		$this->router->registerRoute('POST', '/signup', $this->container->get(SignUpController::class), 'signup_post');
		$this->router->registerRoute('GET', '/signup', $this->container->get(SignUpController::class), 'signup_get');
		$this->router->registerRoute('POST', '/login', $this->container->get(LoginController::class), 'login_post');
		$this->router->registerRoute('GET', '/login', $this->container->get(LoginController::class), 'login_get');
		$this->router->registerRoute('GET', '/resetpassword', $this->container->get(ResetpasswordController::class), 'resetpassword_get');
		$this->router->registerRoute('POST', '/resetpassword', $this->container->get(ResetpasswordController::class), 'resetpassword_post');
		$this->router->registerRoute('GET', '/tree', $this->container->get(TreeController::class), 'tree');
		$this->router->registerRoute('POST', '/contact', $this->container->get(ContactController::class), 'contact_post');
		$this->router->registerRoute('GET', '/contact', $this->container->get(ContactController::class), 'contact_get');
		$this->router->registerRoute('GET', '/update', $this->container->get(UpdateController::class), 'update');
		$this->router->registerRoute('GET', '/[i:error]', $this->container->get(ErrorController::class), 'error');
		$this->router->registerRoute('GET', '/[*]', $this->container->get(ErrorController::class), '404');
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