<?php

declare(strict_types=1);

namespace App\Tests\unit\application\contact\executor;

use App\Application\contact\ContactDAO;
use App\Application\contact\executor\ContactExecutor;
use App\Application\contact\executor\OtherContactExecutor;
use App\Application\redirect\Redirect;
use App\Entity\Contact\Type;
use PHPUnit\Framework\TestCase;

final class ContactExecutorTest extends TestCase
{
    private ContactExecutor $contactExecutor;

    private Redirect $redirect;


    #[\Override]
    protected function setUp(): void
    {

        $contactDAO     = $this->createMock(ContactDAO::class);
        $this->redirect = $this->createMock(Redirect::class);

        $this->contactExecutor = new OtherContactExecutor($contactDAO, $this->redirect);
    }


    public function testGetidReturnsContactTypeId(): void
    {
        $this->assertEquals(Type::OTHER->value, $this->contactExecutor->getId());
    }


    public function testExecuteReturnsErrorWhenFieldsAreMissing(): void
    {

        $result = $this->contactExecutor->execute([]);

        $this->assertSame(
            'Votre prénom doit contenir au moins 1 caractère'
            . '<br>Votre nom doit contenir au moins 1 caractère<br>Votre email doit être valide'
            . '<br>La description doit contenir au moins 1 caractère',
            $result
        );
    }


    public function testExecuteReturnsErrorWhenFieldIsInvalid(): void
    {

        $result = $this->contactExecutor->execute([
            'senderFirstName' => 'a',
            'senderLastName' => 'a',
            'senderEmail' => 'a',
            'message' => 'a',
        ]);

        $this->assertSame('Votre email doit être valide', $result);
    }


    public function testExecuteRedirectToHomeOnSuccess(): void
    {

        $this->redirect->expects($this->once())
            ->method('redirect')
            ->with('home');

        $this->contactExecutor->execute([
            'senderFirstName' => 'a',
            'senderLastName' => 'a',
            'senderEmail' => 'a.a@a.com',
            'message' => 'a'
        ]);
    }
}
