<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Entity\Person\Association;
use App\Entity\Person\Filiere;
use App\Entity\Person\Role;
use App\Entity\Person\School;
use App\Repository\Person\AssociationRepository;
use App\Repository\Person\FiliereRepository;
use App\Repository\Person\SchoolRepository;
use App\Service\MergeService;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(Role::ADMIN->value)]
final class MergeAdminController extends AbstractController
{
    public function __construct(
        private readonly MergeService $mergeService,
        private readonly FiliereRepository $filiereRepository,
        private readonly AssociationRepository $associationRepository,
        private readonly SchoolRepository $schoolRepository,
        private readonly AdminUrlGenerator $adminUrlGenerator,
    ) {
    }

    #[Route('/admin/merge', name: 'admin_merge', methods: ['GET', 'POST'])]
    public function index(Request $request): Response
    {
        $mergeUrl = $this->adminUrlGenerator->setRoute('admin_merge')->generateUrl();

        if ($request->isMethod('POST')) {
            return $this->handleMerge($request, $mergeUrl);
        }

        return $this->render('admin/merge.html.twig', [
            'merge_url'    => $mergeUrl,
            'filieres'     => $this->filiereRepository->findAllOrderedByName(),
            'associations' => $this->associationRepository->findAllOrderedByName(),
            'schools'      => $this->schoolRepository->findAllOrderedByName(),
        ]);
    }

    private function handleMerge(Request $request, string $mergeUrl): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('admin_merge', $request->request->getString('_token'))) {
            $this->addFlash('danger', 'Token CSRF invalide.');
            return $this->redirect($mergeUrl);
        }

        $type     = $request->request->getString('type');
        $sourceId = $request->request->getInt('source_id');
        $targetId = $request->request->getInt('target_id');

        if ($sourceId === $targetId) {
            $this->addFlash('warning', 'La source et la cible sont identiques.');
            return $this->redirect($mergeUrl);
        }

        try {
            $count = match ($type) {
                'filiere'     => $this->mergeFiliere($sourceId, $targetId),
                'association' => $this->mergeAssociation($sourceId, $targetId),
                'school'      => $this->mergeSchool($sourceId, $targetId),
                default       => throw new \InvalidArgumentException('Type inconnu : ' . $type),
            };

            $this->addFlash('success', sprintf('Fusion effectuée : %d enregistrement(s) réassigné(s).', $count));
        } catch (\InvalidArgumentException $invalidArgumentException) {
            $this->addFlash('danger', $invalidArgumentException->getMessage());
        } catch (\Throwable) {
            $this->addFlash('danger', 'Une erreur inattendue est survenue lors de la fusion.');
        }

        return $this->redirect($mergeUrl);
    }

    private function mergeFiliere(int $sourceId, int $targetId): int
    {
        $source = $this->filiereRepository->find($sourceId);
        $target = $this->filiereRepository->find($targetId);

        if (!$source instanceof Filiere || !$target instanceof Filiere) {
            throw new \InvalidArgumentException('Filière source ou cible introuvable.');
        }

        return $this->mergeService->mergeFiliere($source, $target);
    }

    private function mergeAssociation(int $sourceId, int $targetId): int
    {
        $source = $this->associationRepository->find($sourceId);
        $target = $this->associationRepository->find($targetId);

        if (!$source instanceof Association || !$target instanceof Association) {
            throw new \InvalidArgumentException('Association source ou cible introuvable.');
        }

        return $this->mergeService->mergeAssociation($source, $target);
    }

    private function mergeSchool(int $sourceId, int $targetId): int
    {
        $source = $this->schoolRepository->find($sourceId);
        $target = $this->schoolRepository->find($targetId);

        if (!$source instanceof School || !$target instanceof School) {
            throw new \InvalidArgumentException('École source ou cible introuvable.');
        }

        return $this->mergeService->mergeSchool($source, $target);
    }
}
