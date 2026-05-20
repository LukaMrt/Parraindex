<?php

declare(strict_types=1);

namespace App\Controller\Admin\Crud;

use App\Entity\Person\School;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/** @extends AbstractCrudController<School> */
final class SchoolCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return School::class;
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('name', 'Nom');
        yield ImageField::new('logo', 'Logo')
            ->setBasePath('/uploads/schools/')
            ->setUploadDir('public/uploads/schools/')
            ->setUploadedFileNamePattern('[slug]-[timestamp].[extension]')
            ->setRequired(false);
    }

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('École')
            ->setEntityLabelInPlural('Écoles')
            ->setPageTitle(Crud::PAGE_INDEX, 'Liste des écoles')
            ->setPageTitle(Crud::PAGE_NEW, 'Nouvelle école')
            ->setPageTitle(Crud::PAGE_EDIT, fn (School $s): string => 'Modifier ' . ($s->getName() ?? ''))
            ->setDefaultSort(['name' => 'ASC']);
    }
}
