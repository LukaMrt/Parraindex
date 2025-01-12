<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/mentions-legales')]
class LegalNoticeController extends AbstractController
{
    #[Route('/', name: 'legalNotice')]
    public function index(): Response
    {
        return $this->render('legalNotice.html.twig');
    }
}
