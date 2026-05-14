<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Entity\Person\Role;
use App\Service\CsvImportService;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(Role::ADMIN->value)]
final class CsvImportAdminController extends AbstractController
{
    public function __construct(
        private readonly CsvImportService $csvImportService,
        private readonly AdminUrlGenerator $adminUrlGenerator,
    ) {
    }

    #[Route('/admin/persons/import/template', name: 'api_admin_persons_import_template', methods: ['GET'])]
    public function downloadTemplate(): StreamedResponse
    {
        $headers = [
            'firstName',
            'lastName',
            'startYear',
            'biography',
            'description',
            'godFatherFirstName',
            'godFatherLastName',
            'sponsorType',
            'sponsorDate',
            'sponsorDescription',
        ];

        $response = new StreamedResponse(function () use ($headers): void {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                return;
            }
            fputcsv($handle, $headers);
            fputcsv($handle, ['Jean', 'Dupont', '2020', 'Étudiant en BUT', 'Promo 2020', 'Marie', 'Martin', 'CLASSIC', '2021-09-01', 'Parrainage de promo']);
            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="template_import.csv"');

        return $response;
    }

    #[Route('/admin/persons/import', name: 'admin_csv_import', methods: ['GET', 'POST'])]
    public function import(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            return $this->handleImport($request);
        }

        $result  = null;
        $session = $request->getSession();
        if ($session instanceof FlashBagAwareSessionInterface) {
            $flashes = $session->getFlashBag()->get('csv_import_result');
            $flash   = $flashes[0] ?? null;
            if (is_string($flash)) {
                /** @var array<string, mixed> $result */
                $result = json_decode($flash, true);
            }
        }

        return $this->render('admin/csv_import.html.twig', [
            'result' => $result,
        ]);
    }

    private function handleImport(Request $request): RedirectResponse
    {
        $redirectUrl = $this->adminUrlGenerator->setRoute('admin_csv_import')->generateUrl();

        if (!$this->isCsrfTokenValid('csv_import', $request->request->getString('_token'))) {
            $this->addFlash('danger', 'Token CSRF invalide.');
            return $this->redirect($redirectUrl);
        }

        $file = $request->files->get('file');

        if (!$file instanceof UploadedFile) {
            $result = [
                'personsCreated'  => 0,
                'personsSkipped'  => 0,
                'sponsorsCreated' => 0,
                'sponsorsSkipped' => 0,
                'errors'          => ['Aucun fichier reçu.'],
            ];
        } else {
            $csvContent = file_get_contents($file->getPathname());

            if ($csvContent === false || trim($csvContent) === '') {
                $result = [
                    'personsCreated'  => 0,
                    'personsSkipped'  => 0,
                    'sponsorsCreated' => 0,
                    'sponsorsSkipped' => 0,
                    'errors'          => ['Le fichier CSV est vide ou illisible.'],
                ];
            } else {
                $result = $this->csvImportService->import($csvContent);
            }
        }

        $this->addFlash('csv_import_result', json_encode($result));

        return $this->redirect($redirectUrl);
    }
}
