<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Entity\Contact\Contact;
use App\Entity\Contact\Type;
use App\Repository\ContactRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ContactRepositoryTest extends KernelTestCase
{
    private ContactRepository $repository;

    #[\Override]
    protected function setUp(): void
    {
        // Given
        self::bootKernel();
        /** @var ContactRepository $repository */
        $repository       = self::getContainer()->get(ContactRepository::class);
        $this->repository = $repository;
    }

    public function testCreatePersistsContact(): void
    {
        // Given
        $contact = new Contact();
        $contact->setType(Type::ADD_PERSON);
        $contact->setRelatedPersonFirstName('John');
        $contact->setRelatedPersonLastName('Doe');
        $contact->setContacterEmail('test@example.com');
        $contact->setContacterFirstName('Test');
        $contact->setContacterLastName('User');
        $contact->setDescription('Test message');
        $contact->setCreatedAt(new \DateTime());

        // When
        $this->repository->create($contact);

        // Then
        $this->assertGreaterThan(0, $contact->getId());

        // Verify in database
        $savedContact = $this->repository->find($contact->getId());
        $this->assertInstanceOf(Contact::class, $savedContact);
        $this->assertSame(Type::ADD_PERSON, $savedContact->getType());
        $this->assertSame('John', $savedContact->getRelatedPersonFirstName());
    }

    public function testGetAllReturnsAllContacts(): void
    {
        // Given
        $contact1 = new Contact();
        $contact1->setType(Type::BUG);
        $contact1->setContacterEmail('user1@example.com');
        $contact1->setContacterFirstName('User');
        $contact1->setContacterLastName('One');
        $contact1->setDescription('Bug report 1');
        $contact1->setCreatedAt(new \DateTime());

        $contact2 = new Contact();
        $contact2->setType(Type::OTHER);
        $contact2->setContacterEmail('user2@example.com');
        $contact2->setContacterFirstName('User');
        $contact2->setContacterLastName('Two');
        $contact2->setDescription('Other message');
        $contact2->setCreatedAt(new \DateTime());

        $this->repository->create($contact1);
        $this->repository->create($contact2);

        // When
        $result = $this->repository->getAll();

        // Then
        $this->assertNotEmpty($result);
        $this->assertContainsOnlyInstancesOf(Contact::class, $result);
    }

    public function testUpdateModifiesContact(): void
    {
        // Given
        $contact = new Contact();
        $contact->setType(Type::ADD_PERSON);
        $contact->setContacterEmail('original@example.com');
        $contact->setContacterFirstName('Original');
        $contact->setContacterLastName('User');
        $contact->setDescription('Original message');
        $contact->setCreatedAt(new \DateTime());

        $this->repository->create($contact);

        // When
        $contact->setDescription('Updated message');
        $this->repository->update($contact);

        // Then
        $updatedContact = $this->repository->find($contact->getId());
        $this->assertInstanceOf(Contact::class, $updatedContact);
        $this->assertSame('Updated message', $updatedContact->getDescription());
    }

    public function testCreateContactWithAllFields(): void
    {
        // Given
        $contact = new Contact();
        $contact->setType(Type::ADD_SPONSOR);
        $contact->setContacterEmail('complete@example.com');
        $contact->setContacterFirstName('Complete');
        $contact->setContacterLastName('User');
        $contact->setDescription('Complete contact message');
        $contact->setRelatedPersonFirstName('Alice');
        $contact->setRelatedPersonLastName('Smith');
        $contact->setRelatedPerson2FirstName('Bob');
        $contact->setRelatedPerson2LastName('Johnson');
        $contact->setEntryYear(2024);
        $contact->setSponsorType(\App\Entity\Sponsor\Type::HEART);
        $contact->setCreatedAt(new \DateTime());

        // When
        $this->repository->create($contact);

        // Then
        $savedContact = $this->repository->find($contact->getId());
        $this->assertInstanceOf(Contact::class, $savedContact);
        $this->assertSame('Alice', $savedContact->getRelatedPersonFirstName());
        $this->assertSame('Smith', $savedContact->getRelatedPersonLastName());
        $this->assertSame('Bob', $savedContact->getRelatedPerson2FirstName());
        $this->assertSame('Johnson', $savedContact->getRelatedPerson2LastName());
        $this->assertSame(2024, $savedContact->getEntryYear());
        $this->assertSame(\App\Entity\Sponsor\Type::HEART, $savedContact->getSponsorType());
    }
}
