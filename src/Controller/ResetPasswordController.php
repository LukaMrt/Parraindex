<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\AuthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordToken;
use App\Form\ChangePasswordFormType;
use App\Form\ResetPasswordRequestFormType;

class ResetPasswordController extends AbstractController
{
    use ResetPasswordControllerTrait;

    public function __construct(
        private readonly AuthService $authService,
    ) {
    }

    #[Route('/reset-password', name: 'forgot_password_request')]
    public function request(Request $request): Response
    {
        $form = $this->createForm(ResetPasswordRequestFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $email */
            $email = $form->get('email')->getData();

            return $this->processSendingPasswordResetEmail($email);
        }

        return $this->render('reset_password/request.html.twig', ['form' => $form]);
    }

    #[Route('/reset-password/check-email', name: 'check_email')]
    public function checkEmail(): Response
    {
        $token = $this->getTokenObjectFromSession();

        if (!$token instanceof ResetPasswordToken) {
            $token = $this->authService->generateFakeResetToken();
        }

        return $this->render('reset_password/check_email.html.twig', ['token' => $token]);
    }

    #[Route('/reset-password/reset/{token}', name: 'reset_password')]
    public function reset(Request $request, ?string $token = null): Response
    {
        if (!in_array($token, [null, '', '0'], true)) {
            $this->storeTokenInSession($token);

            return $this->redirectToRoute('reset_password');
        }

        $token = $this->getTokenFromSession();

        if ($token === null) {
            throw $this->createNotFoundException('No reset password token found in the URL or in the session.');
        }

        try {
            $user = $this->authService->validateTokenAndFetchUser($token);
        } catch (ResetPasswordExceptionInterface $e) {
            $this->addFlash('reset_password_error', $this->authService->getTranslatedError($e));

            return $this->redirectToRoute('forgot_password_request');
        }

        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->authService->removeResetRequest($token);

            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            $this->authService->resetPassword($user, $plainPassword);
            $this->cleanSessionAfterReset();
            $this->addFlash('success', 'Votre mot de passe a été réinitialisé avec succès.');

            return $this->redirectToRoute('home');
        }

        /** @var FormError $error */
        foreach ($form->getErrors(true) as $error) {
            $this->addFlash('error', $error->getMessage());
        }

        return $this->render('reset_password/reset.html.twig', ['form' => $form]);
    }

    private function processSendingPasswordResetEmail(string $email): RedirectResponse
    {
        $user = $this->authService->findUserByEmail($email);

        if ($user === null) {
            return $this->redirectToRoute('check_email');
        }

        $resetToken = $this->authService->generateAndSendResetToken($user);

        if ($resetToken === null) {
            return $this->redirectToRoute('check_email');
        }

        $this->setTokenObjectInSession($resetToken);

        return $this->redirectToRoute('check_email');
    }
}
