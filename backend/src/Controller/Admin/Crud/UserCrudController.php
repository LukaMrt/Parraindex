<?php

declare(strict_types=1);

namespace App\Controller\Admin\Crud;

use App\Entity\Person\Role;
use App\Entity\Person\User;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/** @extends AbstractCrudController<User> */
final class UserCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly UserPasswordHasherInterface $hasher,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield EmailField::new('email', 'Email');
        yield AssociationField::new('person', 'Personne');
        yield ChoiceField::new('roles', 'Rôles')
            ->setChoices([
                'Utilisateur' => Role::USER->value,
                'Administrateur' => Role::ADMIN->value,
            ])
            ->allowMultipleChoices()
            ->setFormTypeOption('getter', static fn (User $user): array => $user->getRoles())
            ->setFormTypeOption('setter', static fn (User $user, array $roles): User => $user->setRoles(
                array_map(static function (mixed $r): Role {
                    if (!is_string($r)) {
                        throw new \UnexpectedValueException('Expected string role value.');
                    }

                    return Role::from($r);
                }, $roles)
            ));
        yield TextField::new('password', 'Mot de passe')
            ->setFormType(PasswordType::class)
            ->setFormTypeOption('empty_data', '')
            ->setRequired($pageName === 'new')
            ->hideOnIndex()
            ->hideOnDetail()
            ->setHelp('Laisser vide pour ne pas modifier');
        yield BooleanField::new('isVerified', 'Email vérifié');
        yield DateTimeField::new('createdAt', 'Créé le')->hideOnForm();
    }

    #[\Override]
    public function persistEntity(EntityManagerInterface $entityManager, mixed $entityInstance): void
    {
        $this->hashPasswordIfNeeded($entityInstance);
        parent::persistEntity($entityManager, $entityInstance);
    }

    #[\Override]
    public function updateEntity(EntityManagerInterface $entityManager, mixed $entityInstance): void
    {
        $this->hashPasswordIfNeeded($entityInstance);
        parent::updateEntity($entityManager, $entityInstance);
    }

    private function hashPasswordIfNeeded(mixed $entityInstance): void
    {
        if (!$entityInstance instanceof User) {
            return;
        }

        $plain = $entityInstance->getPassword();
        if ($plain === null || $plain === '') {
            return;
        }

        $entityInstance->setPassword($this->hasher->hashPassword($entityInstance, $plain));
    }
}
