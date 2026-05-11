<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Person\Role;
use App\Service\CsvImportService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(Role::ADMIN->value)]
final class CsvImportAdminController extends AbstractController
{
    public function __construct(
        private readonly CsvImportService $csvImportService,
    ) {
    }

    #[Route('/admin/persons/import', name: 'admin_csv_import', methods: ['GET', 'POST'])]
    public function import(Request $request): Response
    {
        $result = null;

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('csv_import', $request->request->getString('_token'))) {
                $this->addFlash('danger', 'Token CSRF invalide.');
                return $this->redirectToRoute('admin_csv_import');
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
        }

        return $this->render('admin/csv_import.html.twig', [
            'result' => $result,
        ]);
    }
}
