<?php

declare(strict_types=1);

namespace App\Controller\Admin\Crud;

use App\Entity\Person\Association;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/** @extends AbstractCrudController<Association> */
final class AssociationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Association::class;
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('name', 'Nom');
        yield ImageField::new('logo', 'Logo')
            ->setBasePath('/uploads/associations/')
            ->setUploadDir('public/uploads/associations/')
            ->setUploadedFileNamePattern('[slug]-[timestamp].[extension]')
            ->setRequired(false);
    }

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Association')
            ->setEntityLabelInPlural('Associations')
            ->setPageTitle(Crud::PAGE_INDEX, 'Liste des associations')
            ->setPageTitle(Crud::PAGE_NEW, 'Nouvelle association')
            ->setPageTitle(Crud::PAGE_EDIT, fn (Association $a): string => 'Modifier ' . ($a->getName() ?? ''))
            ->setDefaultSort(['name' => 'ASC']);
    }
}
