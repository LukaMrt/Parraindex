<?php

declare(strict_types=1);

namespace App\Controller\Admin\Crud;

use App\Entity\Contact\Contact;
use App\Entity\Contact\Type;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/** @extends AbstractCrudController<Contact> */
final class ContactCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Contact::class;
    }

    #[\Override]
    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW, Action::EDIT, Action::DELETE);
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('contacterFirstName', 'Prénom');
        yield TextField::new('contacterLastName', 'Nom');
        yield EmailField::new('contacterEmail', 'Email');
        yield ChoiceField::new('type', 'Type')
            ->setChoices(array_combine(
                array_map(static fn (Type $t): string => $t->name, Type::cases()),
                Type::cases(),
            ));
        yield TextField::new('relatedPersonFirstName', 'Personne concernée (prénom)')->hideOnIndex();
        yield TextField::new('relatedPersonLastName', 'Personne concernée (nom)')->hideOnIndex();
        yield TextField::new('description', 'Description')->hideOnIndex();
        yield DateTimeField::new('createdAt', 'Reçu le');
        yield DateTimeField::new('resolutionDate', 'Résolu le');
    }

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setSearchFields(['contacterFirstName', 'contacterLastName', 'contacterEmail']);
    }
}
