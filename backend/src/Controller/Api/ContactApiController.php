<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Api\ApiResponse;
use App\Dto\Contact\ContactRequestDto;
use App\Entity\Contact\Contact;
use App\Entity\Sponsor\Type as SponsorType;
use App\Service\ContactService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

final class ContactApiController extends AbstractController
{
    public function __construct(
        private readonly ContactService $contactService,
    ) {
    }

    #[Route('/api/contact', name: 'api_contact_create', methods: ['POST'])]
    public function create(#[MapRequestPayload] ContactRequestDto $dto): JsonResponse
    {
        $contact = new Contact();
        $contact->setContacterFirstName($dto->contacterFirstName)
            ->setContacterLastName($dto->contacterLastName)
            ->setContacterEmail($dto->contacterEmail)
            ->setType($dto->type)
            ->setDescription($dto->description)
            ->setCreatedAt(new \DateTime())
            ->setRelatedPersonFirstName($dto->relatedPersonFirstName)
            ->setRelatedPersonLastName($dto->relatedPersonLastName)
            ->setRelatedPerson2FirstName($dto->relatedPerson2FirstName)
            ->setRelatedPerson2LastName($dto->relatedPerson2LastName)
            ->setRegistrationToken($dto->registrationToken);

        if ($dto->entryYear !== null) {
            $contact->setEntryYear($dto->entryYear);
        }

        if ($dto->sponsorType !== null) {
            $sponsorType = SponsorType::tryFrom($dto->sponsorType);
            if ($sponsorType !== null) {
                $contact->setSponsorType($sponsorType);
            }
        }

        if ($dto->sponsorDate !== null) {
            $contact->setSponsorDate(new \DateTime($dto->sponsorDate));
        }

        $this->contactService->create($contact);

        return ApiResponse::success(null, Response::HTTP_CREATED);
    }
}
