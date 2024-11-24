<?php

namespace App\Form;

use App\Entity\Person\Person;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class PersonFormType extends AbstractType
{
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
            ->add('characteristics', CollectionType::class, [
                'entry_type'   => CharacteristicFormType::class,
                'by_reference' => false,
                'keep_as_list' => true,
                'mapped'       => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'         => Person::class,
            'allow_extra_fields' => true,
        ]);
    }
}
