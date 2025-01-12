<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Contact\Contact;
use App\Entity\Contact\Type;
use App\Entity\Sponsor\Type as SponsorType;
use App\Form\ContactType;
use App\Repository\ContactRepository;
use App\Repository\PersonRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/contact')]
class ContactController extends AbstractController
{
    public function __construct(
        private readonly PersonRepository $personRepository,
        private readonly ContactRepository $contactRepository,
    ) {
    }

    #[Route('/contact', name: 'contact', methods: [Request::METHOD_GET])]
    public function index(): Response
    {
        $form   = $this->createForm(ContactType::class);
        $people = [];
        $data   = $this->personRepository->getAll(orderBy: 'lastName');
        foreach ($data as $person) {
            $people[$person->getFullName()] = $person->getFullName();
        }

        return $this->render('contact.html.twig', [
            'contactTypes' => Type::allTitles(),
            'sponsorTypes' => SponsorType::allTitles(),
            'people'       => $people,
            'form'         => $form,
        ]);
    }

    #[Route('/contact', name: 'contact_post', methods: [Request::METHOD_POST])]
    public function post(Request $request): Response
    {
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Contact $contact */
            $contact = $form->getData();
            $this->contactRepository->create($contact);
            $this->addFlash('success', 'Message enregistré');
        }

        /** @var FormError $error */
        foreach ($form->getErrors(true) as $error) {
            $this->addFlash('error', $error->getMessage());
        }

        return $this->redirectToRoute('contact');
    }
}
