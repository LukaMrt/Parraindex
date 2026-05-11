<?php

declare(strict_types=1);

namespace App\Controller\Admin\Crud;

use App\Entity\Person\Person;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ColorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

/** @extends AbstractCrudController<Person> */
final class PersonCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly AdminUrlGenerator $adminUrlGenerator,
    ) {
    }

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

        yield AssociationField::new('godFathers', 'Parrains reçus')
            ->onlyOnDetail()
            ->setCrudController(SponsorCrudController::class)
            ->setHelp('Liens de parrainage où cette personne est filleul');

        yield AssociationField::new('godChildren', 'Filleuls parrainés')
            ->onlyOnDetail()
            ->setCrudController(SponsorCrudController::class)
            ->setHelp('Liens de parrainage où cette personne est parrain');
    }

    #[\Override]
    public function configureActions(Actions $actions): Actions
    {
        $addSponsorAction = Action::new('addSponsor', 'Ajouter un parrainage', 'fa fa-link')
            ->linkToUrl(fn(Person $person): string => $this->adminUrlGenerator
                ->setController(SponsorCrudController::class)
                ->setAction(Action::NEW)
                ->generateUrl())
            ->addCssClass('btn btn-info btn-sm')
            ->displayIf(static fn (): bool => true);

        return $actions
            ->add(Crud::PAGE_DETAIL, $addSponsorAction)
            ->add(Crud::PAGE_INDEX, Action::new('downloadTemplate', 'Télécharger le template CSV', 'fa fa-download')
                ->linkToUrl('/api/admin/persons/import/template')
                ->addCssClass('btn btn-secondary btn-sm')
                ->createAsGlobalAction())
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, static fn (Action $action): Action => $action->setIcon('fa fa-eye')->setLabel('Voir'));
    }

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Personne')
            ->setEntityLabelInPlural('Personnes')
            ->setPageTitle(Crud::PAGE_INDEX, 'Liste des personnes')
            ->setPageTitle(Crud::PAGE_DETAIL, fn (Person $person): string => $person->getFullName())
            ->setPageTitle(Crud::PAGE_EDIT, fn (Person $person): string => 'Modifier ' . $person->getFullName())
            ->setPageTitle(Crud::PAGE_NEW, 'Nouvelle personne')
            ->setDefaultSort(['lastName' => 'ASC']);
    }
}
