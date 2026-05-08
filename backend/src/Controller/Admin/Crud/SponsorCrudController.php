<?php

declare(strict_types=1);

namespace App\Controller\Admin\Crud;

use App\Entity\Sponsor\Sponsor;
use App\Entity\Sponsor\Type;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;

/** @extends AbstractCrudController<Sponsor> */
final class SponsorCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Sponsor::class;
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
