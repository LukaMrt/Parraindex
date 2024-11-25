<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Person\User;
use App\Form\RegistrationFormType;
use App\Repository\PersonRepository;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    public function __construct(
        private readonly Security $security,
        private readonly EmailVerifier $emailVerifier,
        private readonly UserRepository $userRepository,
        private readonly PersonRepository $personRepository,
        private readonly AuthenticationUtils $authenticationUtils,
        private readonly UserPasswordHasherInterface $userPasswordHasher,
    ) {
    }

    #[Route('/login', name: 'login')]
    public function index(): Response
    {
        $error        = $this->authenticationUtils->getLastAuthenticationError();
        $lastUsername = $this->authenticationUtils->getLastUsername();

        if ($error instanceof \Symfony\Component\Security\Core\Exception\AuthenticationException) {
            $this->addFlash('error', 'Identifiants incorrects');
        }

        return $this->render('login.html.twig', [
            'last_username' => $lastUsername,
            'error'         => $error,
        ]);
    }

    #[Route('/logout/success', name: 'logout_success')]
    public function logoutSuccess(): Response
    {
        $this->addFlash('success', 'Vous êtes déconnecté');
        return $this->render('logoutConfirmation.html.twig');
    }

    #[Route('/register', name: 'register', methods: [Request::METHOD_GET])]
    public function register(): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);

        return $this->render('register.html.twig', ['form' => $form]);
    }

    #[Route('/register', name: 'register_handle', methods: [Request::METHOD_POST])]
    #[IsCsrfTokenValid('register', tokenKey: '_csrf_token')]
    public function registerHandler(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->handleForm($form, $user);
        }

        foreach ($form->getErrors(true) as $error) {
            $this->addFlash('error', $error->getMessage());
        }

        return $this->render('register.html.twig', ['form' => $form]);
    }

    private function handleForm(FormInterface $form, User $user): ?Response
    {
        /** @var string $password */
        $password = $form->get('password')->getData();
        // Get firstName and lastName from email
        $emailParts = explode('@', $form->get('email')->getData());
        $emailParts = explode('.', $emailParts[0]);

        $firstName  = ucfirst($emailParts[0]);
        $lastName   = ucfirst($emailParts[1]);
        $person     = $this->personRepository->findOneBy(['firstName' => $firstName, 'lastName' => $lastName]);

        if (null === $person) {
            $this->addFlash('error', 'Personne non trouvée');
            return $this->redirectToRoute('register');
        }

        $user->setPassword($this->userPasswordHasher->hashPassword($user, $password))
            ->setPerson($person)
            ->setCreatedAt(new \DateTimeImmutable());
        $this->userRepository->update($user);

        $this->emailVerifier->sendEmailConfirmation(
            'register_verify',
            $user,
            (new TemplatedEmail())
                ->to((string)$user->getEmail())
                ->subject('Confirmez votre email')
                ->htmlTemplate('registration/confirmation_email.html.twig')
        );

        return $this->security->login($user, 'form_login', 'main');
    }

    #[Route('/register/verify', name: 'register_verify')]
    public function verifyUserEmail(Request $request): Response
    {
        $id = $request->query->get('id');
        if (null === $id) {
            return $this->redirectToRoute('register');
        }

        $user = $this->userRepository->find($id);
        if (null === $user) {
            return $this->redirectToRoute('register');
        }

        $this->emailVerifier->handleEmailConfirmation($request, $user);
        $this->addFlash('success', 'Votre email a été vérifié');
        return $this->redirectToRoute('home');
    }

    #[Route('/register/success', name: 'register_success')]
    public function registerSuccess(): Response
    {
        return $this->render('signupConfirmation.html.twig');
    }
}
