<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Person\Person;
use App\Entity\Person\User;
use App\Security\Voter\PersonVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DataController extends AbstractController
{
    #[Route(path: '/data/download/{id}', name: 'data_download')]
    #[IsGranted(PersonVoter::DOWNLOAD_DATA, subject: 'person')]
    public function download(?Person $person): JsonResponse
    {
        $person = $this->resolvePerson($person);

        if (!$person instanceof Person) {
            return $this->json(
                ['data' => "Aucune donnée n'a été trouvée"],
                Response::HTTP_NOT_FOUND,
            );
        }

        return $this->json($person);
    }

    private function resolvePerson(?Person $person): ?Person
    {
        if ($person instanceof Person) {
            return $person;
        }

        /** @var ?User $user */
        $user = $this->getUser();

        return $user instanceof User && $user->isAdmin() ? $user->getPerson() : null;
    }
}
