<?php

declare(strict_types=1);

namespace App\Controller\Admin\Crud;

use App\Entity\Person\PersonAssociation;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/** @extends AbstractCrudController<PersonAssociation> */
final class PersonAssociationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PersonAssociation::class;
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield AssociationField::new('person', 'Personne');
        yield AssociationField::new('association', 'Association');
        yield TextField::new('poste', 'Poste');
        yield DateField::new('startDate', 'Date de début')->setRequired(false);
        yield DateField::new('endDate', 'Date de fin')->setRequired(false);
    }

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Participation')
            ->setEntityLabelInPlural('Participations aux associations')
            ->setPageTitle(Crud::PAGE_INDEX, 'Participations aux associations')
            ->setPageTitle(Crud::PAGE_NEW, 'Nouvelle participation')
            ->setPageTitle(Crud::PAGE_EDIT, fn (PersonAssociation $pa): string => 'Modifier — ' . ($pa->getAssociation()?->getName() ?? ''))
            ->setDefaultSort(['id' => 'DESC']);
    }
}
