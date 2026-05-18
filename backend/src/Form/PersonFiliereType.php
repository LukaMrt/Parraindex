<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Person\Filiere;
use App\Entity\Person\PersonFiliere;
use App\Repository\FiliereRepository;
use App\Form\FiliereNameType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;

class PersonFiliereType extends AbstractType
{
    public function __construct(
        private readonly FiliereRepository $filiereRepository,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('filiereName', FiliereNameType::class, [
                'mapped'      => false,
                'label'       => 'Filière',
                'constraints' => [new NotBlank()],
            ])
            ->add('startYear', IntegerType::class, [
                'label'       => 'Année de début',
                'constraints' => [new NotBlank(), new Positive()],
            ])
            ->add('endYear', IntegerType::class, [
                'label'    => 'Année de fin',
                'required' => false,
            ]);

        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event): void {
            /** @var PersonFiliere|null $personFiliere */
            $personFiliere = $event->getData();
            if ($personFiliere?->getFiliere() !== null) {
                $event->getForm()->get('filiereName')->setData(
                    $personFiliere->getFiliere()->getName()
                );
            }
        });

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event): void {
            /** @var PersonFiliere $personFiliere */
            $personFiliere = $event->getData();
            $name = $event->getForm()->get('filiereName')->getData();

            if (!is_string($name) || $name === '') {
                return;
            }

            $canonical = ucfirst(strtolower(trim($name)));

            $filiere = $this->filiereRepository->findOneBy(['name' => $canonical])
                ?? (new Filiere())->setName($canonical);

            $personFiliere->setFiliere($filiere);
        });
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        $datalistId = 'filiere-dl-' . $view['filiereName']->vars['id'];
        $view['filiereName']->vars['attr']['list'] = $datalistId;
        $view['filiereName']->vars['filiere_names'] = $options['filiere_names'];
        $view['filiereName']->vars['datalist_id']   = $datalistId;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'    => PersonFiliere::class,
            'filiere_names' => [],
        ]);
    }
}