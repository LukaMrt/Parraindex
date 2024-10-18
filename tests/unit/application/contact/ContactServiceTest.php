<?php

declare(strict_types=1);

namespace App\Tests\unit\application\contact;

use App\Application\contact\ContactDAO;
use App\Application\contact\ContactService;
use App\Application\contact\executor\ContactExecutor;
use App\Application\contact\executor\ContactExecutors;
use App\Entity\Contact\Type;
use App\Entity\old\contact\DefaultContact;
use PHPUnit\Framework\TestCase;

final class ContactServiceTest extends TestCase
{
    private ContactExecutors $contactExecutors;

    private ContactExecutor $contactExecutor;

    private ContactDAO $contactDAO;

    private ContactService $contactService;


    #[\Override]
    protected function setUp(): void
    {
        $this->contactExecutors = $this->createMock(ContactExecutors::class);
        $this->contactExecutor  = $this->createMock(ContactExecutor::class);
        $this->contactDAO       = $this->createMock(ContactDAO::class);
        $this->contactService   = new ContactService($this->contactExecutors, $this->contactDAO);
    }


    public function testRegistercontactReturnsErrorWhenTypeIsMissing(): void
    {
        $this->contactExecutors->method('getExecutorsById')
            ->with(-1)
            ->willReturn([]);

        $result = $this->contactService->registerContact([]);

        $this->assertSame("Le type de contact n'est pas valide.", $result);
    }


    public function testRegistercontactReturnsErrorWhenTypeIsNotValid(): void
    {

        $this->contactExecutors->method('getExecutorsById')
            ->with(12345678)
            ->willReturn([]);

        $result = $this->contactService->registerContact(['type' => '12345678']);
        $this->assertSame("Le type de contact n'est pas valide.", $result);
    }


    public function testRegistercontactReturnsErrorWhenExecutorReturnsAnError(): void
    {

        $this->contactExecutors->method('getExecutorsById')
            ->with(1)
            ->willReturn([1 => $this->contactExecutor]);

        $this->contactExecutor->method('execute')
            ->with(['type' => '1'])
            ->willReturn('Une erreur est survenue.');

        $result = $this->contactService->registerContact(['type' => '1']);

        $this->assertSame('Une erreur est survenue.', $result);
    }


    public function testRegistercontactReturnsNothingWhenExecutorReturnsNothing(): void
    {

        $this->contactExecutors->method('getExecutorsById')
            ->with(1)
            ->willReturn([$this->contactExecutor]);

        $this->contactExecutor->method('execute')
            ->with(['type' => '1'])
            ->willReturn('');

        $result = $this->contactService->registerContact(['type' => '1']);

        $this->assertSame('', $result);
    }


    public function testClosecontactCallsClosecontactOnDAO(): void
    {

        $this->contactDAO->expects($this->once())
            ->method('closeContact')
            ->with(1, 1);

        $this->contactService->closeContact(1, 1);
    }


    public function testGetcontactReturnsWantendContact(): void
    {

        $contact  = new DefaultContact(1, date('Y-m-d'), null, '', '', Type::OTHER, '');
        $contact2 = new DefaultContact(2, date('Y-m-d'), null, '', '', Type::OTHER, '');

        $this->contactDAO->method('getContactList')
            ->willReturn([$contact, $contact2]);

        $result = $this->contactService->getContact(2);

        $this->assertEquals($contact2, $result);
    }
}
