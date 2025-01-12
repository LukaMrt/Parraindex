<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Person\Person;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class PersonFormType extends AbstractType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName')
            ->add('lastName')
            ->add('biography')
            ->add('color')
            ->add('description')
            ->add('picture', FileType::class, [
                'mapped'      => false,
                'required'    => false,
                'constraints' => [
                    new Assert\File([
                        'maxSize'   => '1024k',
                        'mimeTypes' => [
                            'image/*',
                        ],
                    ])
                ],
            ])
            ->add('characteristics', CollectionType::class, ['entry_type' => CharacteristicFormType::class])
            ->addEventListener(FormEvents::PRE_SUBMIT, fn (FormEvent $event) => $this->preSubmit($event))
        ;
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'         => Person::class,
            'allow_extra_fields' => true,
        ]);
    }

    private function preSubmit(FormEvent $event): void
    {
        /** @var array<string, mixed> $data */
        $data = $event->getData();
        /** @var array<array<string, string>> $characteristics */
        $characteristics = $data['characteristics'] ?? [];

        $data['characteristics'] = array_map(
            fn (array $characteristic): array => [
                'id'      => $characteristic['id'],
                'visible' => ($characteristic['visible'] ?? '') === 'on',
                'value'   => $characteristic['value'] ?? '',
            ],
            array_filter(
                $characteristics,
                static fn(array $characteristic): bool => isset($characteristic['id'])
                    && ($characteristic['id'] !== ''
                    && $characteristic['id'] !== '0')
            ),
        );
        $event->setData($data);
    }
}
