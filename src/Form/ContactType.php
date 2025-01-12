<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Contact\Contact;
use App\Entity\Contact\Type;
use App\Entity\Sponsor\Type as SponsorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\PostSubmitEvent;
use Symfony\Component\Form\Event\PreSubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('contacterFirstName')
            ->add('contacterLastName')
            ->add('contacterEmail')
            ->add('type', EnumType::class, ['class' => Type::class])
            ->add('description')
            ->add('relatedPerson', TextType::class, ['mapped' => false])
            ->add('relatedPersonBis', TextType::class, ['mapped' => false])
            ->add('relatedPersonFirstName')
            ->add('relatedPersonLastName')
            ->add('entryYear')
            ->add('relatedPerson2', TextType::class, ['mapped' => false])
            ->add('relatedPerson2FirstName')
            ->add('relatedPerson2LastName')
            ->add('sponsorType', EnumType::class, ['class' => SponsorType::class])
            ->add('sponsorDate')
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Les mots de passe ne correspondent pas.',
            ])
            ->addEventListener(
                FormEvents::PRE_SUBMIT,
                fn(PreSubmitEvent $event) => $this->preSubmit($event)
            )
            ->addEventListener(
                FormEvents::POST_SUBMIT,
                fn(PostSubmitEvent $event) => $this->postSubmit($event)
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'         => Contact::class,
            'allow_extra_fields' => true,
        ]);
    }

    private function preSubmit(PreSubmitEvent $event): void
    {
        /** @var array<string, string> $data */
        $data = $event->getData();

        $typesWithRelatedPerson = [
            Type::ADD_SPONSOR,
            Type::UPDATE_SPONSOR,
            Type::REMOVE_SPONSOR,
        ];
        if (in_array(Type::from(intval($data['type'])), $typesWithRelatedPerson)) {
            $names                          = explode(' ', $data['relatedPerson']);
            $data['relatedPersonFirstName'] = $names[0];
            $data['relatedPersonLastName']  = $names[1];
            unset($data['relatedPerson']);
        }

        $typesWithRelatedPersonBis = [
            Type::UPDATE_PERSON,
            Type::REMOVE_PERSON,
            Type::CHOCKING_CONTENT,
        ];
        if (in_array(Type::from(intval($data['type'])), $typesWithRelatedPersonBis)) {
            $names                          = explode(' ', $data['relatedPersonBis']);
            $data['relatedPersonFirstName'] = $names[0];
            $data['relatedPersonLastName']  = $names[1];
            unset($data['relatedPerson']);
        }

        $typesWithRelatedPerson2 = [
            Type::ADD_SPONSOR,
            Type::UPDATE_SPONSOR,
            Type::REMOVE_SPONSOR,
        ];
        if (in_array(Type::from(intval($data['type'])), $typesWithRelatedPerson2)) {
            $names                           = explode(' ', $data['relatedPerson2']);
            $data['relatedPerson2FirstName'] = $names[0];
            $data['relatedPerson2LastName']  = $names[1];
            unset($data['relatedPerson2']);
        }

        $event->setData($data);
    }

    private function postSubmit(PostSubmitEvent $event): void
    {
        /** @var Contact $contact */
        $contact = $event->getForm()->getData();
        $contact->setCreatedAt(new \DateTime());
    }
}
