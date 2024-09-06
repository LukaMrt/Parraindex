<?php

namespace App\infrastructure\injector;

use App\application\contact\ContactDAO;
use App\application\contact\executor\AddPersonContactExecutor;
use App\application\contact\executor\AddSponsorContactExecutor;
use App\application\contact\executor\BugContactExecutor;
use App\application\contact\executor\ChockingContentContactExecutor;
use App\application\contact\executor\ContactExecutors;
use App\application\contact\executor\OtherContactExecutor;
use App\application\contact\executor\PasswordContactExecutor;
use App\application\contact\executor\RemovePersonContactExecutor;
use App\application\contact\executor\RemoveSponsorContactExecutor;
use App\application\contact\executor\UpdatePersonContactExecutor;
use App\application\contact\executor\UpdateSponsorContactExecutor;
use App\application\logging\Logger;
use App\application\login\AccountDAO;
use App\application\login\SessionManager;
use App\application\login\UrlUtils;
use App\application\mail\Mailer;
use App\application\person\characteristic\CharacteristicDAO;
use App\application\person\characteristic\CharacteristicTypeDAO;
use App\application\person\PersonDAO;
use App\application\random\Random;
use App\application\redirect\Redirect;
use App\application\sponsor\SponsorDAO;
use App\controller\AboutController;
use App\controller\ContactAdminController;
use App\controller\ContactCloseController;
use App\controller\ContactController;
use App\controller\DataDownloadController;
use App\controller\EditPersonController;
use App\controller\EditSponsorController;
use App\controller\ErrorController;
use App\controller\HomeController;
use App\controller\LegalNoticeController;
use App\controller\LoginController;
use App\controller\LogoutConfirmationController;
use App\controller\LogoutController;
use App\controller\PersonController;
use App\controller\RemoveSponsorController;
use App\controller\ResetpasswordConfirmationController;
use App\controller\ResetpasswordController;
use App\controller\ResetpasswordValidationController;
use App\controller\SignUpConfirmationController;
use App\controller\SignUpController;
use App\controller\SignUpValidationController;
use App\controller\SponsorController;
use App\controller\TreeController;
use App\infrastructure\account\MysqlAccountDAO;
use App\infrastructure\contact\MysqlContactDAO;
use App\infrastructure\database\DatabaseConnection;
use App\infrastructure\logging\MonologLogger;
use App\infrastructure\login\DefaultUrlUtils;
use App\infrastructure\mail\PhpMailer;
use App\infrastructure\person\characteristic\MysqlCharacteristicDAO;
use App\infrastructure\person\characteristic\MysqlCharacteristicTypeDAO;
use App\infrastructure\person\MySqlPersonDAO;
use App\infrastructure\random\DefaultRandom;
use App\infrastructure\redirect\HttpRedirect;
use App\infrastructure\router\Router;
use App\infrastructure\session\DefaultSessionManager;
use App\infrastructure\sponsor\MySqlSponsorDAO;
use DI\Container;
use DI\ContainerBuilder;
use DI\DependencyException;
use DI\NotFoundException;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;
use function DI\get;

/**
 * Injector class used to inject dependencies in all classes
 */
class Injector
{
    /**
     * @var Container DI container
     */
    private Container $container;
    /**
     * @var Router $router Router
     */
    private Router $router;


    /**
     * @param Router $router Router
     */
    public function __construct(Router $router)
    {
        $this->container = ContainerBuilder::buildDevContainer();
        $this->router = $router;
    }


    /**
     * Register all dependencies in the container
     * @return void
     */
    public function build(): void
    {

        $twig = $this->buildTwig();
        $databaseConnection = new DatabaseConnection();
        $userDAO = get(MySqlPersonDAO::class);
        $accountDAO = get(MySqlAccountDAO::class);
        $sessionManager = get(DefaultSessionManager::class);
        $redirect = get(HttpRedirect::class);
        $personDAO = get(MySqlPersonDAO::class);
        $contactDAO = get(MySqlContactDAO::class);
        $sponsorDAO = get(MySqlSponsorDAO::class);
        $characteristicDAO = get(MysqlCharacteristicDAO::class);
        $characteristicTypeDAO = get(MysqlCharacteristicTypeDAO::class);
        $logger = get(MonologLogger::class);
        $mailer = get(PhpMailer::class);
        $random = get(DefaultRandom::class);
        $urlUtils = get(DefaultUrlUtils::class);

        $this->container->set(Environment::class, $twig);
        $this->container->set(DatabaseConnection::class, $databaseConnection);
        $this->container->set(Router::class, $this->router);
        $this->container->set(Router::class, $this->router);
        $this->container->set(Redirect::class, $redirect);
        $this->container->set(Logger::class, $logger);
        $this->container->set(Mailer::class, $mailer);
        $this->container->set(Random::class, $random);
        $this->container->set(UrlUtils::class, $urlUtils);
        $this->container->set(SessionManager::class, $sessionManager);

        $this->container->set(ContactExecutors::class, fn(Container $container) => new ContactExecutors([
            $container->get(AddPersonContactExecutor::class),
            $container->get(UpdatePersonContactExecutor::class),
            $container->get(RemovePersonContactExecutor::class),
            $container->get(AddSponsorContactExecutor::class),
            $container->get(UpdateSponsorContactExecutor::class),
            $container->get(ChockingContentContactExecutor::class),
            $container->get(BugContactExecutor::class),
            $container->get(RemoveSponsorContactExecutor::class),
            $container->get(OtherContactExecutor::class),
            $container->get(PasswordContactExecutor::class)
        ]));

        $this->container->set(PersonDAO::class, $userDAO);
        $this->container->set(AccountDAO::class, $accountDAO);
        $this->container->set(PersonDAO::class, $personDAO);
        $this->container->set(ContactDAO::class, $contactDAO);
        $this->container->set(SponsorDAO::class, $sponsorDAO);
        $this->container->set(CharacteristicDAO::class, $characteristicDAO);
        $this->container->set(CharacteristicTypeDAO::class, $characteristicTypeDAO);
    }


    /**
     * Build twig environment
     * @return Environment Twig environment
     */
    private function buildTwig(): Environment
    {
        $twig = new Environment(new FilesystemLoader(Injector . phpdirname(__FILE__, 4) . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR));

        $twig->addFunction(new TwigFunction('style', fn(string $path) => '/css/' . $path));
        $twig->addFunction(new TwigFunction('script', fn(string $path) => '/js/' . $path));
        $twig->addFunction(new TwigFunction('image', fn(string $path) => '/img/' . $path));
        $twig->addFunction(new TwigFunction('picture', fn(string $path) => '/img/pictures/' . $path));
        $twig->addFunction(new TwigFunction('icon', fn(string $path) => '/img/icons/' . $path));

        return $twig;
    }


    /**
     * Sets up all the routes with the controllers
     * @throws DependencyException when a dependency cannot be resolved
     * @throws NotFoundException when a dependency cannot be resolved
     */
    public function setUpRouter(): void
    {
        $this->router->registerRoute('GET', '/', $this->container->get(HomeController::class), 'home');
        $this->router->registerRoute('GET', '/a-propos', $this->container->get(AboutController::class), 'about');
        $this->router->registerRoute('GET', '/mentions-legales', $this->container->get(legalnoticeController::class), 'legalnotice');

        $this->router->registerRoute('GET', '/admin/contact', $this->container->get(ContactAdminController::class), 'contact_admin');
        $this->router->registerRoute('GET', '/admin/contact/[i:id]/delete', $this->container->get(ContactCloseController::class), 'contact_close');
        $this->router->registerRoute('GET', '/admin/contact/[i:id]/delete/[*:resolve]', $this->container->get(ContactCloseController::class), 'contact_close_resolve');

        $this->router->registerRoute('GET', '/contact', $this->container->get(ContactController::class), 'contact_get');
        $this->router->registerRoute('POST', '/contact', $this->container->get(ContactController::class), 'contact_post');

        $this->router->registerRoute('GET', '/connexion', $this->container->get(LoginController::class), 'login_get');
        $this->router->registerRoute('POST', '/connexion', $this->container->get(LoginController::class), 'login_post');
        $this->router->registerRoute('GET', '/deconnexion', $this->container->get(LogoutController::class), 'logout_get');
        $this->router->registerRoute('GET', '/deconnexion/confirmation', $this->container->get(LogoutConfirmationController::class), 'logout_confirmation');

        $this->router->registerRoute('GET', '/inscription', $this->container->get(SignUpController::class), 'signup_get');
        $this->router->registerRoute('POST', '/inscription', $this->container->get(SignUpController::class), 'signup_post');
        $this->router->registerRoute('GET', '/inscription/confirmation', $this->container->get(SignUpConfirmationController::class), 'signup_confirmation');
        $this->router->registerRoute('GET', '/inscription/validation/[*:token]', $this->container->get(SignUpValidationController::class), 'signup_validation');

        $this->router->registerRoute('GET', '/mot-de-passe/reinitialisation', $this->container->get(ResetpasswordController::class), 'resetpassword_get');
        $this->router->registerRoute('POST', '/mot-de-passe/reinitialisation', $this->container->get(ResetpasswordController::class), 'resetpassword_post');
        $this->router->registerRoute('GET', '/mot-de-passe/reinitialisation/confirmation', $this->container->get(ResetpasswordConfirmationController::class), 'resetpassword_confirmation');
        $this->router->registerRoute('GET', '/mot-de-passe/reinitialisation/validation/[*:token]', $this->container->get(ResetpasswordValidationController::class), 'resetpassword_validation');

        $this->router->registerRoute('GET', '/personne/[i:id]', $this->container->get(PersonController::class), 'person');
        $this->router->registerRoute('GET', '/personne/[i:id]/edition', $this->container->get(EditPersonController::class), 'editperson_get');
        $this->router->registerRoute('POST', '/personne/[i:id]/edition', $this->container->get(EditPersonController::class), 'editperson_post');
        $this->router->registerRoute('PUT', '/personne/[i:id]/edition', $this->container->get(EditPersonController::class), 'editperson_put');
        $this->router->registerRoute('DELETE', '/personne/[i:id]/edition', $this->container->get(EditPersonController::class), 'editperson_delete');
        $this->router->registerRoute('GET', '/personne/[i:id]/donnees', $this->container->get(DataDownloadController::class), 'person_data');

        $this->router->registerRoute('GET', '/parrainage/[i:id]', $this->container->get(SponsorController::class), 'sponsor');
        $this->router->registerRoute('GET', '/parrainage/[i:id]/edition', $this->container->get(EditSponsorController::class), 'editsponsor_get');
        $this->router->registerRoute('POST', '/parrainage/[i:id]/edition', $this->container->get(EditSponsorController::class), 'editsponsor_post');
        $this->router->registerRoute('GET', '/parrainage/[i:id]/remove', $this->container->get(RemoveSponsorController::class), 'removesponsor');

        $this->router->registerRoute('GET', '/arbre', $this->container->get(TreeController::class), 'tree');

        $this->router->registerRoute('GET', '/[i:error]', $this->container->get(ErrorController::class), 'error');
        $this->router->registerRoute('GET', '/[*]', $this->container->get(ErrorController::class), '404');
    }
}
