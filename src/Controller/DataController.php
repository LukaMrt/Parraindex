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

#[Route(path: '/data')]
class DataController extends AbstractController
{
    #[Route(path: '/download/{id}', name: 'data_download')]
    #[IsGranted(PersonVoter::PERSON_DATA_DOWNLOAD, subject: 'person')]
    public function download(?Person $person): JsonResponse
    {
        $person = $this->getPerson($person);

        if (!$person instanceof \App\Entity\Person\Person) {
            return $this->json(
                [
                    'data' => "Aucune donnée n'a été trouvée",
                ],
                Response::HTTP_NOT_FOUND,
            );
        }

        return $this->json($person);
    }

    public function getPerson(?Person $person): ?Person
    {
        /** @var ?User $user */
        $user  = $this->getUser();
        $admin = $user instanceof User && $user->isAdmin();
        return $person instanceof Person ? $person : ($admin ? $user->getPerson() : null);
    }
}
