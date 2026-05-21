<?php

declare(strict_types=1);

namespace App\Controller\Admin\Crud;

use App\Entity\Person\PersonFiliere;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/** @extends AbstractCrudController<PersonFiliere> */
final class PersonFiliereCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PersonFiliere::class;
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield AssociationField::new('person', 'Personne');
        yield AssociationField::new('filiere', 'Filière');
        yield AssociationField::new('school', 'École')->setRequired(false);
        yield IntegerField::new('startYear', 'Année de début');
        yield IntegerField::new('endYear', 'Année de fin')->setRequired(false);
        yield TextField::new('diplomaName', 'Nom du diplôme')->setRequired(false);
    }

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Filière de personne')
            ->setEntityLabelInPlural('Filières des personnes')
            ->setPageTitle(Crud::PAGE_INDEX, 'Filières des personnes')
            ->setPageTitle(Crud::PAGE_NEW, 'Nouvelle inscription en filière')
            ->setPageTitle(Crud::PAGE_EDIT, fn (PersonFiliere $pf): string => 'Modifier — ' . ($pf->getFiliere()?->getName() ?? ''))
            ->setDefaultSort(['id' => 'DESC']);
    }
}
