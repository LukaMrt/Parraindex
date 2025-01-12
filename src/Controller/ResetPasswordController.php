<?php

declare(strict_types=1);

namespace App\Controller;

use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordToken;
use App\Entity\Person\User;
use App\Form\ChangePasswordFormType;
use App\Form\ResetPasswordRequestFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

#[Route('/reset-password')]
class ResetPasswordController extends AbstractController
{
    use ResetPasswordControllerTrait;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ResetPasswordHelperInterface $resetPasswordHelper,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly TranslatorInterface $translator,
        private readonly MailerInterface $mailer,
    ) {
    }

    #[Route('', name: 'forgot_password_request')]
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

    #[Route('/check-email', name: 'check_email')]
    public function checkEmail(): Response
    {
        $token = $this->getTokenObjectFromSession();
        if (!$token instanceof ResetPasswordToken) {
            $token = $this->resetPasswordHelper->generateFakeResetToken();
        }

        return $this->render('reset_password/check_email.html.twig', ['token' => $token]);
    }

    #[Route('/reset/{token}', name: 'reset_password')]
    public function reset(Request $request, ?string $token = null): Response
    {
        if ($token !== null && $token !== '' && $token !== '0') {
            $this->storeTokenInSession($token);
            return $this->redirectToRoute('reset_password');
        }

        $token = $this->getTokenFromSession();
        if ($token === null) {
            throw $this->createNotFoundException('No reset password token found in the URL or in the session.');
        }

        try {
            /** @var User $user */
            $user = $this->resetPasswordHelper->validateTokenAndFetchUser($token);
        } catch (ResetPasswordExceptionInterface $resetPasswordException) {
            $this->addFlash('reset_password_error', sprintf(
                '%s - %s',
                $this->translator->trans(
                    ResetPasswordExceptionInterface::MESSAGE_PROBLEM_VALIDATE,
                    [],
                    'ResetPasswordBundle'
                ),
                $this->translator->trans($resetPasswordException->getReason(), [], 'ResetPasswordBundle')
            ));

            return $this->redirectToRoute('forgot_password_request');
        }

        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->resetPasswordHelper->removeResetRequest($token);

            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));
            $this->entityManager->flush();
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
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

        if ($user === null) {
            return $this->redirectToRoute('check_email');
        }

        try {
            $resetToken = $this->resetPasswordHelper->generateResetToken($user);
        } catch (ResetPasswordExceptionInterface) {
            return $this->redirectToRoute('check_email');
        }

        $email = new TemplatedEmail()
            ->from(new Address('parraindex@parraindex.com', 'Parraindex'))
            ->to((string) $user->getEmail())
            ->subject('Your password reset request')
            ->htmlTemplate('reset_password/email.html.twig')
            ->context([
                'resetToken' => $resetToken,
            ])
        ;

        $this->mailer->send($email);

        $this->setTokenObjectInSession($resetToken);

        return $this->redirectToRoute('check_email');
    }
}
