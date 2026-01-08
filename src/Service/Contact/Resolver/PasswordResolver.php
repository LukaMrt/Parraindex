<?php

declare(strict_types=1);

namespace App\Service\Contact\Resolver;

use App\Entity\Contact\Contact;
use App\Entity\Contact\Type;
use App\Entity\Person\Person;
use App\Entity\Person\User;
use App\Repository\ContactRepository;
use App\Repository\PersonRepository;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use App\Service\Contact\ContactResolverInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class PasswordResolver extends AbstractController implements ContactResolverInterface
{
    public function __construct(
        private PersonRepository $personRepository,
        private UserRepository $userRepository,
        private ContactRepository $contactRepository,
        private EmailVerifier $emailVerifier,
        private UserPasswordHasherInterface $userPasswordHasher,
    ) {
    }

    public function supports(Contact $contact): bool
    {
        return $contact->getType() === Type::PASSWORD;
    }

    public function resolve(Contact $contact): ?Response
    {
        $person = $this->personRepository->getByIdentity(
            $contact->getContacterFirstName(),
            $contact->getContacterLastName()
        );

        if (!$person instanceof Person) {
            throw new NotFoundHttpException('Personne non trouvée');
        }

        $registrationToken = bin2hex(random_bytes(32));
        $contact->setRegistrationToken($registrationToken);
        $this->contactRepository->update($contact);

        $user = new User()->setPerson($person)->setEmail($contact->getContacterEmail());
        $temporaryPassword = bin2hex(random_bytes(32));
        $user->setPassword($this->userPasswordHasher->hashPassword($user, $temporaryPassword));

        $this->userRepository->update($user);
        $this->emailVerifier->sendEmailConfirmation(
            'register_verify',
            $user,
            new TemplatedEmail()
                ->to((string)$user->getEmail())
                ->subject('Confirmez votre email et définissez votre mot de passe')
                ->htmlTemplate('registration/confirmation_email.html.twig')
                ->context(['registrationToken' => $registrationToken])
        );

        return null;
    }
}
