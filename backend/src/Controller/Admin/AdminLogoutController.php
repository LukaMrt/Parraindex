<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

final class AdminLogoutController extends AbstractController
{
    #[Route('/admin/logout', name: 'app_admin_logout')]
    public function logout(): never
    {
        throw new \LogicException('This method is intercepted by the firewall.');
    }
}
