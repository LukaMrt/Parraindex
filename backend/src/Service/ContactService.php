<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Contact\Contact;
use App\Repository\ContactRepository;
use App\Service\Contact\ContactResolverManager;
use Symfony\Component\HttpFoundation\Response;

final readonly class ContactService
{
    public function __construct(
        private ContactRepository $contactRepository,
        private ContactResolverManager $resolverManager,
    ) {
    }

    public function create(Contact $contact): void
    {
        $this->contactRepository->create($contact);
    }

    /**
     * @return Contact[]
     */
    public function getAll(): array
    {
        return $this->contactRepository->getAll();
    }

    public function close(Contact $contact): void
    {
        $contact->setResolutionDate(new \DateTime());
        $this->contactRepository->update($contact);
    }

    public function resolve(Contact $contact): ?Response
    {
        return $this->resolverManager->resolve($contact);
    }
}
