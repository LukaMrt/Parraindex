<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Person\User;
use App\Form\RegistrationFormType;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    public function __construct(
        private readonly Security $security,
        private readonly UserService $userService,
        private readonly AuthenticationUtils $authenticationUtils,
    ) {
    }

    #[Route('/login', name: 'login')]
    public function index(): Response
    {
        $error        = $this->authenticationUtils->getLastAuthenticationError();
        $lastUsername = $this->authenticationUtils->getLastUsername();

        if ($error instanceof AuthenticationException) {
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
        $form = $this->createForm(RegistrationFormType::class, new User());

        return $this->render('register.html.twig', ['form' => $form]);
    }

    #[Route('/register', name: 'register_handle', methods: [Request::METHOD_POST])]
    #[IsCsrfTokenValid('register', tokenKey: '_csrf_token')]
    public function registerHandler(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            foreach ($form->getErrors(true) as $error) {
                $this->addFlash('error', $error->getMessage());
            }

            return $this->render('register.html.twig', ['form' => $form]);
        }

        /** @var string $plainPassword */
        $plainPassword = $form->get('password')->getData();

        try {
            $this->userService->register($user, $plainPassword);
        } catch (\RuntimeException $e) {
            $this->addFlash('error', $e->getMessage());

            return $this->redirectToRoute('register');
        }

        $this->userService->sendVerificationEmail($user);

        $response = $this->security->login($user, 'form_login', 'main');

        return $response instanceof Response ? $response : $this->redirectToRoute('register_success');
    }

    #[Route('/register/verify', name: 'register_verify')]
    public function verifyUserEmail(Request $request): Response
    {
        $id = $request->query->get('id');

        if (null === $id) {
            return $this->redirectToRoute('register');
        }

        $user = $this->userService->findById((int) $id);

        if (null === $user) {
            return $this->redirectToRoute('register');
        }

        $this->userService->verifyEmail($request, $user);
        $this->addFlash('success', 'Votre email a été vérifié');

        return $this->redirectToRoute('home');
    }

    #[Route('/register/success', name: 'register_success')]
    public function registerSuccess(): Response
    {
        return $this->render('signupConfirmation.html.twig');
    }
}
