<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Contact\Contact;
use App\Entity\Contact\Type;
use App\Entity\Sponsor\Type as SponsorType;
use App\Form\ContactType;
use App\Service\ContactService;
use App\Service\PersonService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ContactController extends AbstractController
{
    public function __construct(
        private readonly PersonService $personService,
        private readonly ContactService $contactService,
    ) {
    }

    #[Route('/contact/contact', name: 'contact', methods: [Request::METHOD_GET])]
    public function index(): Response
    {
        $form   = $this->createForm(ContactType::class);
        $people = [];

        foreach ($this->personService->getAll(orderBy: 'lastName') as $person) {
            $people[$person->getFullName()] = $person->getFullName();
        }

        return $this->render('contact.html.twig', [
            'contactTypes' => Type::allTitles(),
            'sponsorTypes' => SponsorType::allTitles(),
            'people'       => $people,
            'form'         => $form,
        ]);
    }

    #[Route('/contact/contact', name: 'contact_post', methods: [Request::METHOD_POST])]
    public function post(Request $request): Response
    {
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Contact $contact */
            $contact = $form->getData();
            $this->contactService->create($contact);
            $this->addFlash('success', 'Message enregistré');
        }

        /** @var FormError $error */
        foreach ($form->getErrors(true) as $error) {
            $this->addFlash('error', $error->getMessage());
        }

        return $this->redirectToRoute('contact');
    }
}
