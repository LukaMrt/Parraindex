<?php

namespace application\login;

use App\application\login\AccountDAO;
use App\application\login\LoginService;
use App\application\login\SessionManager;
use App\application\redirect\Redirect;
use App\model\account\Password;
use PHPUnit\Framework\TestCase;

class LoginServiceTest extends TestCase {

    private LoginService $loginService;
    private Redirect $redirect;
    private AccountDAO $accountDAO;
    private SessionManager $sessionManager;

    public function setUp(): void {
        $this->accountDAO = $this->createMock(AccountDAO::class);
        $this->redirect = $this->createMock(Redirect::class);
        $this->sessionManager = $this->createMock(SessionManager::class);
        $this->loginService = new LoginService($this->accountDAO, $this->redirect, $this->sessionManager);
    }

    public function testDetectsMissingFields(): void {

        // Test 1
        $return = $this->loginService->login(array());

        $this->assertEquals('Veuillez remplir tous les champs', $return);

        // Test 2
        $return = $this->loginService->login(array(
            'login' => 'test'
        ));

        $this->assertEquals('Veuillez remplir tous les champs', $return);
    }

    public function testDetectsInvalidLogin(): void {

        // Test 1

        $this->accountDAO->method('getAccountPassword')
            ->with('test')
            ->willReturn(new Password(''));

        $return = $this->loginService->login(array(
            'login' => 'test',
            'password' => 'test',
        ));

        $this->assertEquals('Identifiant incorrect', $return);
    }

    public function testSavesLoginInSessionOnSuccess(): void {

        $this->accountDAO->method('getAccountPassword')
            ->with('test')
            ->will($this->onConsecutiveCalls(new Password(password_hash('test', PASSWORD_DEFAULT)), new Password('')));

        $this->sessionManager->expects($this->once())
            ->method('set')
            ->with('login', 'test');

        $this->loginService->login(array(
            'login' => 'test',
            'password' => 'test',
        ));

        $this->loginService->login(array(
            'login' => 'test',
            'password' => 'test',
        ));
    }

    public function testReturnsNothingOnSuccess(): void {

        $this->accountDAO->method('getAccountPassword')
            ->with('test')
            ->willReturn(new Password(password_hash('test', PASSWORD_DEFAULT)));

        $return = $this->loginService->login(array(
            'login' => 'test',
            'password' => 'test',
        ));

        $this->assertEquals('', $return);
    }

    public function testRedirectToHomeOnSuccess(): void {

        $this->accountDAO->method('getAccountPassword')
            ->with('test')
            ->will($this->onConsecutiveCalls(new Password(password_hash('test', PASSWORD_DEFAULT)), new Password('')));

        $this->redirect->expects($this->once())
            ->method('redirect')
            ->with('home');

        $this->loginService->login(array(
            'login' => 'test',
            'password' => 'test',
        ));

        $this->loginService->login(array(
            'login' => 'test',
            'password' => 'test',
        ));
    }

}
