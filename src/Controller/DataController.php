<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Person\Role;
use App\Entity\Person\User;
use App\Repository\PersonRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/data')]
class DataController extends AbstractController
{
    public function __construct(
        private readonly Security $security,
        private readonly PersonRepository $personRepository,
    ) {
    }

    #[Route(path: '/download/{id}', name: 'data_download')]
    #[IsGranted(Role::USER->value)]
    public function download(?int $id = null): JsonResponse
    {
        /** @var User $user */
        $user     = $this->security->getUser();
        $personId = ($user->isAdmin() && $id) ? $id : $user->getPerson()->getId();
        $person   = $this->personRepository->find($personId);

        if (!$person) {
            return $this->json(
                [
                    'data' => "Aucune donnée n'a été trouvée",
                ],
                Response::HTTP_NOT_FOUND,
            );
        }

        return $this->json(
            $person,
            Response::HTTP_OK,
        );
    }
}
