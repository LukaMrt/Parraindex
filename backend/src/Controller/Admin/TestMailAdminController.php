<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Person\Role;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(Role::ADMIN->value)]
final class TestMailAdminController extends AbstractController
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly AdminUrlGenerator $adminUrlGenerator,
        private readonly string $mailUser,
        private readonly string $mailName,
    ) {
    }

    #[Route('/admin/test-mail', name: 'admin_test_mail', methods: ['GET', 'POST'])]
    public function index(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            return $this->handleSend($request);
        }

        return $this->render('admin/test_mail.html.twig');
    }

    private function handleSend(Request $request): RedirectResponse
    {
        $origin      = $request->request->getString('_origin');
        $redirectUrl = $origin === 'dashboard'
            ? $this->adminUrlGenerator->setRoute('admin')->generateUrl()
            : $this->adminUrlGenerator->setRoute('admin_test_mail')->generateUrl();

        if (!$this->isCsrfTokenValid('test_mail', $request->request->getString('_token'))) {
            $this->addFlash('danger', 'Token CSRF invalide.');
            return $this->redirect($redirectUrl);
        }

        $recipient = trim($request->request->getString('email'));

        if ($recipient === '' || filter_var($recipient, FILTER_VALIDATE_EMAIL) === false) {
            $this->addFlash('danger', 'Adresse email invalide.');
            return $this->redirect($redirectUrl);
        }

        $email = new Email()
            ->from(new Address($this->mailUser, $this->mailName))
            ->to($recipient)
            ->subject('[Parraindex] Email de test')
            ->html('<p>Ceci est un email de test envoyé depuis le tableau de bord Parraindex. Si vous recevez ce message, l\'envoi d\'emails fonctionne correctement.</p>');

        try {
            $this->mailer->send($email);
            $this->addFlash('success', sprintf('Email de test envoyé avec succès à %s.', $recipient));
        } catch (TransportExceptionInterface $transportException) {
            $this->addFlash('danger', sprintf('Échec de l\'envoi : %s', $transportException->getMessage()));
        }

        return $this->redirect($redirectUrl);
    }
}
