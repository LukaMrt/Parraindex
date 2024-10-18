<?php

declare(strict_types=1);

namespace App\Tests\unit\application\contact\executor;

use App\Application\contact\ContactDAO;
use App\Application\contact\executor\BugContactExecutor;
use App\Application\redirect\Redirect;
use App\Entity\Contact\Type;
use App\Entity\old\contact\DefaultContact;
use PHPUnit\Framework\TestCase;

final class BugContactExecutorTest extends TestCase
{
    private BugContactExecutor $bugContactExecutor;

    private ContactDAO $contactDAO;

    private array $defaultArray = [
        'senderFirstName' => 'test1',
        'senderLastName' => 'test2',
        'senderEmail' => 'test.test@test.com',
        'message' => 'empty'
    ];


    #[\Override]
    protected function setUp(): void
    {

        $this->contactDAO = $this->createMock(ContactDAO::class);
        $redirect         = $this->createMock(Redirect::class);

        $this->bugContactExecutor = new BugContactExecutor($this->contactDAO, $redirect);
    }


    public function testExecuteReturnsErrorWhenSenderFirstnameIsMissing(): void
    {

        $this->defaultArray['senderFirstName'] = '';

        $result = $this->bugContactExecutor->execute($this->defaultArray);

        $this->assertSame('Votre prénom doit contenir au moins 1 caractère', $result);
    }


    public function testExecuteSuccessSavesContactWithGivenValues(): void
    {

        $defaultContact = new DefaultContact(
            -1,
            date('Y-m-d'),
            null,
            'test1 test2',
            'test.test@test.com',
            Type::BUG,
            'empty',
        );

        $this->contactDAO->expects($this->once())
            ->method('saveSimpleContact')
            ->with($defaultContact);

        $this->bugContactExecutor->execute($this->defaultArray);
    }
}
