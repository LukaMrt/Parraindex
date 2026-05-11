<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Api\ApiError;
use App\Api\ApiResponse;
use App\Api\ErrorCode;
use App\Dto\Person\PersonRequestDto;
use App\Entity\Contact\Contact;
use App\Entity\Person\Person;
use App\Entity\Person\Role;
use App\Service\ContactService;
use App\Service\CsvImportService;
use App\Service\PersonService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(Role::ADMIN->value)]
final class AdminApiController extends AbstractController
{
    public function __construct(
        private readonly ContactService $contactService,
        private readonly PersonService $personService,
        private readonly CsvImportService $csvImportService,
    ) {
    }

    #[Route('/api/admin/contacts', name: 'api_admin_contacts_list', methods: ['GET'])]
    public function listContacts(): JsonResponse
    {
        $contacts = $this->contactService->getAll();

        return ApiResponse::success(array_map(
            static fn (Contact $c): array => [
                'id'                   => $c->getId(),
                'contacterFirstName'   => $c->getContacterFirstName(),
                'contacterLastName'    => $c->getContacterLastName(),
                'contacterEmail'       => $c->getContacterEmail(),
                'type'                 => $c->getType()?->value,
                'description'          => $c->getDescription(),
                'createdAt'            => $c->getCreatedAt()?->format('Y-m-d H:i:s'),
                'resolutionDate'       => $c->getResolutionDate()?->format('Y-m-d H:i:s'),
                'relatedPersonFirstName' => $c->getRelatedPersonFirstName(),
                'relatedPersonLastName'  => $c->getRelatedPersonLastName(),
            ],
            $contacts,
        ));
    }

    #[Route('/api/admin/contacts/{id}', name: 'api_admin_contacts_resolve', methods: ['PUT'])]
    public function resolveContact(Contact $contact): JsonResponse
    {
        if ($contact->getResolutionDate() instanceof \DateTimeInterface) {
            return ApiResponse::error(
                new ApiError(ErrorCode::VALIDATION_ERROR, 'Cette demande est déjà traitée'),
                Response::HTTP_UNPROCESSABLE_ENTITY,
            );
        }

        $this->contactService->resolve($contact);
        $this->contactService->close($contact);

        return ApiResponse::success(null);
    }

    #[Route('/api/admin/persons', name: 'api_admin_persons_create', methods: ['POST'])]
    public function createPerson(#[MapRequestPayload] PersonRequestDto $dto): JsonResponse
    {
        $existing = $this->personService->findByIdentity($dto->firstName, $dto->lastName);

        if ($existing instanceof Person) {
            return ApiResponse::error(
                new ApiError(ErrorCode::VALIDATION_ERROR, 'Une personne avec ce nom existe déjà'),
                Response::HTTP_UNPROCESSABLE_ENTITY,
            );
        }

        $person = new Person();
        $person->setFirstName($dto->firstName)
            ->setLastName($dto->lastName)
            ->setStartYear($dto->startYear)
            ->setBiography($dto->biography)
            ->setDescription($dto->description);

        if ($dto->color !== null) {
            $person->setColor($dto->color);
        }

        $this->personService->update($person);

        return ApiResponse::success($this->personService->mapToResponseDto($person), Response::HTTP_CREATED);
    }

    #[Route('/api/admin/persons/import/template', name: 'api_admin_persons_import_template', methods: ['GET'])]
    public function downloadImportTemplate(): StreamedResponse
    {
        $csvContent = $this->csvImportService->generateTemplate();

        $response = new StreamedResponse(static function () use ($csvContent): void {
            echo $csvContent;
        });

        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="import_personnes_template.csv"');

        return $response;
    }

    #[Route('/api/admin/persons/import/csv', name: 'api_admin_persons_import_csv', methods: ['POST'])]
    public function importPersonsCsv(Request $request): JsonResponse
    {
        $file = $request->files->get('file');

        if (!$file instanceof UploadedFile) {
            return ApiResponse::error(
                new ApiError(ErrorCode::VALIDATION_ERROR, 'Aucun fichier envoyé. Utilisez le champ "file".'),
                Response::HTTP_UNPROCESSABLE_ENTITY,
            );
        }

        $mimeType  = $file->getMimeType() ?? '';
        $extension = strtolower($file->getClientOriginalExtension());

        $allowedMimes = [
            'text/csv',
            'text/plain',
            'application/csv',
            'application/vnd.ms-excel',
        ];
        if ($extension !== 'csv' && !in_array($mimeType, $allowedMimes, true)) {
            return ApiResponse::error(
                new ApiError(ErrorCode::VALIDATION_ERROR, 'Le fichier doit être au format CSV.'),
                Response::HTTP_UNPROCESSABLE_ENTITY,
            );
        }

        $csvContent = file_get_contents($file->getPathname());

        if ($csvContent === false || trim($csvContent) === '') {
            return ApiResponse::error(
                new ApiError(ErrorCode::VALIDATION_ERROR, 'Le fichier CSV est vide ou illisible.'),
                Response::HTTP_UNPROCESSABLE_ENTITY,
            );
        }

        $result = $this->csvImportService->import($csvContent);

        return ApiResponse::success($result, Response::HTTP_OK);
    }
}
