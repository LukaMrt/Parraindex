<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\Admin\Crud\AssociationCrudController;
use App\Controller\Admin\Crud\ContactCrudController;
use App\Controller\Admin\Crud\FiliereCrudController;
use App\Controller\Admin\Crud\PendingUserCrudController;
use App\Controller\Admin\Crud\PersonAssociationCrudController;
use App\Controller\Admin\Crud\PersonCrudController;
use App\Controller\Admin\Crud\PersonFiliereCrudController;
use App\Controller\Admin\Crud\SchoolCrudController;
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
        return $this->render('admin/dashboard.html.twig');
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
        yield MenuItem::section('Référentiel');
        yield MenuItem::linkTo(FiliereCrudController::class, 'Filières', 'fa fa-graduation-cap');
        yield MenuItem::linkTo(PersonFiliereCrudController::class, 'Filières des personnes', 'fa fa-list');
        yield MenuItem::linkTo(SchoolCrudController::class, 'Écoles', 'fa fa-school');
        yield MenuItem::linkTo(AssociationCrudController::class, 'Associations', 'fa fa-users');
        yield MenuItem::linkTo(PersonAssociationCrudController::class, 'Participations associations', 'fa fa-id-badge');
        yield MenuItem::section('Parrainages');
        yield MenuItem::linkTo(SponsorCrudController::class, 'Parrainages', 'fa fa-link');
        yield MenuItem::section('Demandes');
        yield MenuItem::linkTo(ContactCrudController::class, 'Contacts', 'fa fa-envelope');
        yield MenuItem::section('Outils');
        $mergeUrl = $this->adminUrlGenerator->setRoute('admin_merge')->generateUrl();
        yield MenuItem::linkToUrl('Fusionner référentiels', 'fa fa-code-merge', $mergeUrl);
        yield MenuItem::linkToUrl('Test mail', 'fa fa-paper-plane', $this->adminUrlGenerator->setRoute('admin_test_mail')->generateUrl());
        yield MenuItem::section();
        yield MenuItem::linkToUrl('Retour au site', 'fa fa-arrow-left', '/');
    }
}
