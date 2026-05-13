<?php

declare(strict_types=1);

namespace App\Controller\Admin\Crud;

use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Entity\Person\User;
use App\Repository\UserRepository;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

/** @extends AbstractCrudController<User> */
final class PendingUserCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly AdminUrlGenerator $adminUrlGenerator,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Compte en attente')
            ->setEntityLabelInPlural('Comptes en attente de validation')
            ->setDefaultSort(['createdAt' => 'ASC'])
            ->showEntityActionsInlined();
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield EmailField::new('email', 'Email');
        yield AssociationField::new('person', 'Personne');
        yield DateTimeField::new('createdAt', 'Inscrit le')->hideOnForm();
    }

    #[\Override]
    public function createIndexQueryBuilder(
        SearchDto $searchDto,
        EntityDto $entityDto,
        FieldCollection $fields,
        FilterCollection $filters,
    ): QueryBuilder {
        return parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters)
            ->andWhere('entity.isValidated = false');
    }

    #[\Override]
    public function configureActions(Actions $actions): Actions
    {
        $validate = Action::new('validate', 'Valider', 'fa fa-check')
            ->linkToCrudAction('validateUser')
            ->addCssClass('btn btn-success btn-sm');

        $reject = Action::new('reject', 'Rejeter', 'fa fa-times')
            ->linkToCrudAction('rejectUser')
            ->addCssClass('btn btn-danger btn-sm');

        return $actions
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
            ->remove(Crud::PAGE_INDEX, Action::DELETE)
            ->add(Crud::PAGE_INDEX, $validate)
            ->add(Crud::PAGE_INDEX, $reject)
            ->disable(Action::NEW, Action::EDIT, Action::DELETE, Action::DETAIL);
    }

    /** @param AdminContext<User> $context */
    #[AdminRoute('/pending-user/validate/{entityId}', name: 'pending_user_validate')]
    public function validateUser(AdminContext $context): RedirectResponse
    {
        /** @var User $user */
        $user = $context->getEntity()->getInstance();
        $user->setValidated(true);

        $this->userRepository->update($user);

        $this->addFlash('success', sprintf('Compte de %s validé.', $user->getPerson()?->getFirstName()));

        return $this->redirect(
            $this->adminUrlGenerator
                ->setController(self::class)
                ->setAction(Action::INDEX)
                ->generateUrl()
        );
    }

    /** @param AdminContext<User> $context */
    #[AdminRoute('/pending-user/reject/{entityId}', name: 'pending_user_reject')]
    public function rejectUser(AdminContext $context): RedirectResponse
    {
        /** @var User $user */
        $user = $context->getEntity()->getInstance();
        $name = $user->getPerson()?->getFirstName() . ' ' . $user->getPerson()?->getLastName();
        $this->userRepository->delete($user);

        $this->addFlash('warning', sprintf('Compte de %s rejeté et supprimé.', $name));

        return $this->redirect(
            $this->adminUrlGenerator
                ->setController(self::class)
                ->setAction(Action::INDEX)
                ->generateUrl()
        );
    }
}
