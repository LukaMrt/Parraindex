<?php

declare(strict_types=1);

namespace App\Controller\Admin\Crud;

use App\Entity\Person\Filiere;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ColorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/** @extends AbstractCrudController<Filiere> */
final class FiliereCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Filiere::class;
    }

    #[\Override]
    public function createEntity(string $entityFqcn): Filiere
    {
        return new Filiere();
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('name', 'Nom');
        yield TextField::new('color', 'Couleur')
            ->formatValue(static function (?string $color): string {
                if ($color === null) {
                    return '—';
                }

                $dot = sprintf(
                    '<span style="display:inline-block;width:14px;height:14px;border-radius:50%%;background:%s;border:1px solid rgba(0,0,0,.15)"></span>',
                    $color,
                );

                return sprintf('<span style="display:inline-flex;align-items:center;gap:6px">%s%s</span>', $dot, $color);
            })
            ->renderAsHtml()
            ->hideOnForm();

        yield ColorField::new('color', 'Couleur')
            ->setFormTypeOptions(['attr' => ['data-randomize' => 'true']])
            ->onlyOnForms();
    }

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Filière')
            ->setEntityLabelInPlural('Filières')
            ->setPageTitle(Crud::PAGE_INDEX, 'Liste des filières')
            ->setPageTitle(Crud::PAGE_NEW, 'Nouvelle filière')
            ->setPageTitle(Crud::PAGE_EDIT, fn (Filiere $f): string => 'Modifier ' . ($f->getName() ?? ''))
            ->setDefaultSort(['name' => 'ASC'])
            ->setFormThemes(['@EasyAdmin/crud/form_theme.html.twig', 'admin/form/filiere_color_theme.html.twig']);
    }
}
