<?php

namespace unit\application\contact\executor;

use App\application\contact\ContactDAO;
use App\application\contact\executor\OtherContactExecutor;
use App\application\redirect\Redirect;
use App\model\contact\ContactType;
use App\model\contact\DefaultContact;
use PHPUnit\Framework\TestCase;

class OtherContactExecutorTest extends TestCase
{

    private OtherContactExecutor $executor;

    private ContactDAO $contactDAO;

    private array $defaultArray = [
        'senderFirstName' => 'test1',
        'senderLastName' => 'test2',
        'senderEmail' => 'test.test@test.com',
        'message' => 'empty'
    ];


    public function setUp(): void
    {

        $this->contactDAO = $this->createMock(ContactDAO::class);
        $redirect = $this->createMock(Redirect::class);

        $this->executor = new OtherContactExecutor($this->contactDAO, $redirect);
    }


    public function testExecuteReturnsErrorWhenSenderFirstnameIsMissing()
    {

        $this->defaultArray['senderFirstName'] = '';

        $result = $this->executor->execute($this->defaultArray);

        $this->assertEquals('Votre prénom doit contenir au moins 1 caractère', $result);
    }


    public function testExecuteSuccessSavesContactWithGivenValues(): void
    {

        $contact = new DefaultContact(
            -1,
            date('Y-m-d'),
            null,
            'test1 test2',
            'test.test@test.com',
            ContactType::OTHER,
            'empty',
        );

        $this->contactDAO->expects($this->once())
            ->method('saveSimpleContact')
            ->with($contact);

        $this->executor->execute($this->defaultArray);
    }

}