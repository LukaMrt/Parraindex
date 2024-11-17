<?php

declare(strict_types=1);

namespace App\Infrastructure\old\injector;

use App\Application\contact\ContactDAO;
use App\Application\contact\executor\AddPersonContactExecutor;
use App\Application\contact\executor\AddSponsorContactExecutor;
use App\Application\contact\executor\BugContactExecutor;
use App\Application\contact\executor\ChockingContentContactExecutor;
use App\Application\contact\executor\ContactExecutors;
use App\Application\contact\executor\OtherContactExecutor;
use App\Application\contact\executor\PasswordContactExecutor;
use App\Application\contact\executor\RemovePersonContactExecutor;
use App\Application\contact\executor\RemoveSponsorContactExecutor;
use App\Application\contact\executor\UpdatePersonContactExecutor;
use App\Application\contact\executor\UpdateSponsorContactExecutor;
use App\Application\logging\Logger;
use App\Application\login\AccountDAO;
use App\Application\login\SessionManager;
use App\Application\login\UrlUtils;
use App\Application\mail\Mailer;
use App\Application\person\characteristic\CharacteristicDAO;
use App\Application\person\characteristic\CharacteristicTypeDAO;
use App\Application\person\PersonDAO;
use App\Application\random\Random;
use App\Application\redirect\Redirect;
use App\Application\sponsor\SponsorDAO;
use App\Controller\AboutController;
use App\Controller\ContactAdminController;
use App\Controller\ContactCloseController;
use App\Controller\ContactController;
use App\Controller\DataDownloadController;
use App\Controller\EditPersonController;
use App\Controller\EditSponsorController;
use App\Controller\ErrorController;
use App\Controller\HomeController;
use App\Controller\LegalNoticeController;
use App\Controller\SecurityController;
use App\Controller\LogoutConfirmationController;
use App\Controller\LogoutController;
use App\Controller\PersonController;
use App\Controller\RemoveSponsorController;
use App\Controller\ResetpasswordConfirmationController;
use App\Controller\ResetpasswordController;
use App\Controller\ResetpasswordValidationController;
use App\Controller\SignUpConfirmationController;
use App\Controller\SignUpController;
use App\Controller\SignUpValidationController;
use App\Controller\SponsorController;
use App\Controller\TreeController;
use App\Infrastructure\old\account\MysqlAccountDAO;
use App\Infrastructure\old\contact\MysqlContactDAO;
use App\Infrastructure\old\database\DatabaseConnection;
use App\Infrastructure\old\logging\MonologLogger;
use App\Infrastructure\old\login\DefaultUrlUtils;
use App\Infrastructure\old\mail\PhpMailer;
use App\Infrastructure\old\person\characteristic\MysqlCharacteristicDAO;
use App\Infrastructure\old\person\characteristic\MysqlCharacteristicTypeDAO;
use App\Infrastructure\old\person\MySqlPersonDAO;
use App\Infrastructure\old\random\DefaultRandom;
use App\Infrastructure\old\redirect\HttpRedirect;
use App\Infrastructure\old\router\Router;
use App\Infrastructure\old\session\DefaultSessionManager;
use App\Infrastructure\old\sponsor\MySqlSponsorDAO;
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
        $this->router    = $router;
    }


    /**
     * Register all dependencies in the container
     */
    public function build(): void
    {

        $twigEnvironment                  = $this->buildTwig();
        $databaseConnection    = new DatabaseConnection();
        $userDAO               = get(MySqlPersonDAO::class);
        $accountDAO            = get(MySqlAccountDAO::class);
        $sessionManager        = get(DefaultSessionManager::class);
        $redirect              = get(HttpRedirect::class);
        $personDAO             = get(MySqlPersonDAO::class);
        $contactDAO            = get(MySqlContactDAO::class);
        $sponsorDAO            = get(MySqlSponsorDAO::class);
        $characteristicDAO     = get(MysqlCharacteristicDAO::class);
        $characteristicTypeDAO = get(MysqlCharacteristicTypeDAO::class);
        $logger                = get(MonologLogger::class);
        $mailer                = get(PhpMailer::class);
        $random                = get(DefaultRandom::class);
        $urlUtils              = get(DefaultUrlUtils::class);

        $this->container->set(Environment::class, $twigEnvironment);
        $this->container->set(DatabaseConnection::class, $databaseConnection);
        $this->container->set(Router::class, $this->router);
        $this->container->set(Router::class, $this->router);
        $this->container->set(Redirect::class, $redirect);
        $this->container->set(Logger::class, $logger);
        $this->container->set(Mailer::class, $mailer);
        $this->container->set(Random::class, $random);
        $this->container->set(UrlUtils::class, $urlUtils);
        $this->container->set(SessionManager::class, $sessionManager);

        $this->container->set(ContactExecutors::class, fn(Container $container): \App\Application\contact\executor\ContactExecutors => new ContactExecutors([
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
        $twigEnvironment = new Environment(new FilesystemLoader(Injector . phpdirname(__FILE__, 4) . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR));

        $twigEnvironment->addFunction(new TwigFunction('styles', fn(string $path): string => '/css/' . $path));
        $twigEnvironment->addFunction(new TwigFunction('scripts', fn(string $path): string => '/scripts/' . $path));
        $twigEnvironment->addFunction(new TwigFunction('images', fn(string $path): string => '/images/' . $path));
        $twigEnvironment->addFunction(new TwigFunction('picture', fn(string $path): string => '/images/pictures/' . $path));
        $twigEnvironment->addFunction(new TwigFunction('icon', fn(string $path): string => '/images/icons/' . $path));

        return $twigEnvironment;
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

        $this->router->registerRoute('GET', '/connexion', $this->container->get(SecurityController::class), 'login_get');
        $this->router->registerRoute('POST', '/connexion', $this->container->get(SecurityController::class), 'login_post');
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
