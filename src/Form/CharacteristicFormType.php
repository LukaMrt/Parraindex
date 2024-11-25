<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Characteristic\Characteristic;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CharacteristicFormType extends AbstractType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('id')
            ->add('visible')
            ->add('value');
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'         => Characteristic::class,
            'allow_extra_fields' => true,
        ]);
    }
}
