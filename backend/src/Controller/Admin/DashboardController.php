<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\Admin\Crud\ContactCrudController;
use App\Controller\Admin\Crud\PendingUserCrudController;
use App\Controller\Admin\Crud\PersonCrudController;
use App\Controller\Admin\Crud\SponsorCrudController;
use App\Controller\Admin\Crud\UserCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
final class DashboardController extends AbstractDashboardController
{
    public function __construct(private readonly AdminUrlGenerator $adminUrlGenerator)
    {
    }

    #[\Override]
    public function index(): Response
    {
        return $this->render('@EasyAdmin/page/content.html.twig');
    }

    #[\Override]
    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Parraindex — Admin')
            ->setFaviconPath('favicon.ico');
    }

    #[\Override]
    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Tableau de bord', 'fa fa-home');
        yield MenuItem::section('Personnes');
        yield MenuItem::linkTo(PersonCrudController::class, 'Personnes', 'fa fa-user');
        yield MenuItem::linkToUrl('Importer CSV', 'fa fa-upload', $this->adminUrlGenerator->setRoute('admin_csv_import')->generateUrl());
        yield MenuItem::linkTo(UserCrudController::class, 'Comptes', 'fa fa-lock');
        yield MenuItem::linkTo(PendingUserCrudController::class, 'En attente de validation', 'fa fa-clock');
        yield MenuItem::section('Parrainages');
        yield MenuItem::linkTo(SponsorCrudController::class, 'Parrainages', 'fa fa-link');
        yield MenuItem::section('Demandes');
        yield MenuItem::linkTo(ContactCrudController::class, 'Contacts', 'fa fa-envelope');
        yield MenuItem::section();
        yield MenuItem::linkToUrl('Retour au site', 'fa fa-arrow-left', '/');
    }
}
