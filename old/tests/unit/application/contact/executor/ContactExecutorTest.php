<?php

namespace unit\application\contact\executor;

use App\application\contact\ContactDAO;
use App\application\contact\executor\ContactExecutor;
use App\application\contact\executor\OtherContactExecutor;
use App\application\redirect\Redirect;
use App\model\contact\ContactType;
use PHPUnit\Framework\TestCase;

class ContactExecutorTest extends TestCase
{
    private ContactExecutor $executor;

    private Redirect $redirect;


    public function setUp(): void
    {

        $contactDAO = $this->createMock(ContactDAO::class);
        $this->redirect = $this->createMock(Redirect::class);

        $this->executor = new OtherContactExecutor($contactDAO, $this->redirect);
    }


    public function testGetidReturnsContactTypeId(): void
    {
        $this->assertEquals(ContactType::OTHER->value, $this->executor->getId());
    }


    public function testExecuteReturnsErrorWhenFieldsAreMissing(): void
    {

        $result = $this->executor->execute([]);

        $this->assertEquals(
            'Votre prénom doit contenir au moins 1 caractère'
            . '<br>Votre nom doit contenir au moins 1 caractère<br>Votre email doit être valide'
            . '<br>La description doit contenir au moins 1 caractère',
            $result
        );
    }


    public function testExecuteReturnsErrorWhenFieldIsInvalid(): void
    {

        $result = $this->executor->execute([
            'senderFirstName' => 'a',
            'senderLastName' => 'a',
            'senderEmail' => 'a',
            'message' => 'a',
        ]);

        $this->assertEquals('Votre email doit être valide', $result);
    }


    public function testExecuteRedirectToHomeOnSuccess(): void
    {

        $this->redirect->expects($this->once())
            ->method('redirect')
            ->with('home');

        $this->executor->execute([
            'senderFirstName' => 'a',
            'senderLastName' => 'a',
            'senderEmail' => 'a.a@a.com',
            'message' => 'a'
        ]);
    }
}
