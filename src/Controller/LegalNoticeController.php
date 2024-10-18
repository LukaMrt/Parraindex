<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LegalNoticeController extends AbstractController
{
    #[Route('/mentions-legales', name: 'legalNotice')]
    public function index(): Response
    {
        return $this->render('legalNotice.html.twig');
    }
}
