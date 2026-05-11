<?php

declare(strict_types=1);

namespace App\Controller\Admin\Crud;

use App\Entity\Sponsor\Sponsor;
use App\Entity\Sponsor\Type;
use App\Repository\PersonRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;

/** @extends AbstractCrudController<Sponsor> */
final class SponsorCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly PersonRepository $personRepository,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Sponsor::class;
    }

    #[\Override]
    public function createEntity(string $entityFqcn): Sponsor
    {
        $sponsor = new Sponsor();
        $context = $this->getContext();
        $request = $context?->getRequest();

        $godFatherId = $request?->query->getInt('godFather') ?? 0;
        $godChildId  = $request?->query->getInt('godChild') ?? 0;

        if ($godFatherId > 0) {
            $sponsor->setGodFather($this->personRepository->find($godFatherId));
        }

        if ($godChildId > 0) {
            $sponsor->setGodChild($this->personRepository->find($godChildId));
        }

        return $sponsor;
    }

    #[\Override]
    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_DETAIL, Action::EDIT)
            ->add(Crud::PAGE_DETAIL, Action::DELETE);
    }

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Parrainage')
            ->setEntityLabelInPlural('Parrainages')
            ->setPageTitle(Crud::PAGE_INDEX, 'Liste des parrainages')
            ->setPageTitle(Crud::PAGE_NEW, 'Nouveau parrainage')
            ->setDefaultSort(['id' => 'DESC']);
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield AssociationField::new('godFather', 'Parrain');
        yield AssociationField::new('godChild', 'Filleul');
        yield ChoiceField::new('type', 'Type')
            ->setChoices([
                'Parrainage de cœur' => Type::HEART,
                'Parrainage IUT'     => Type::CLASSIC,
                'Inconnu'            => Type::UNKNOWN,
            ]);
        yield DateField::new('date', 'Date')->hideOnIndex();
        yield TextareaField::new('description', 'Description')->hideOnIndex();
        yield DateField::new('createdAt', 'Créé le')->hideOnForm();
    }
}
