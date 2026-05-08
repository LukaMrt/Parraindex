<?php

declare(strict_types=1);

namespace App\Controller\Admin\Crud;

use App\Entity\Person\Person;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ColorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/** @extends AbstractCrudController<Person> */
final class PersonCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Person::class;
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('firstName', 'Prénom');
        yield TextField::new('lastName', 'Nom');
        yield IntegerField::new('startYear', 'Année d\'entrée');
        yield ColorField::new('color', 'Couleur');
        yield DateField::new('birthdate', 'Date de naissance')->hideOnIndex();
        yield TextareaField::new('biography', 'Biographie')->hideOnIndex();
        yield TextareaField::new('description', 'Description')->hideOnIndex();
        yield DateField::new('createdAt', 'Créé le')->hideOnForm();
    }
}
