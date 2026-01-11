<?php

namespace App\Form;

use App\Entity\Consumption;
use App\Entity\Lodgment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class WelcomeFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Données de logement
            ->add('lodgment_type', TextType::class, [
                'label' => 'Type de logement',
                'mapped' => false,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le type de logement est obligatoire',
                    ]),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: Appartement, Maison...',
                ],
            ])
            ->add('surface', IntegerType::class, [
                'label' => 'Surface (m²)',
                'mapped' => false,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'La surface est obligatoire',
                    ]),
                    new Assert\Positive([
                        'message' => 'La surface doit être un nombre positif',
                    ]),
                    new Assert\Range([
                        'min' => 10,
                        'max' => 1000,
                        'notInRangeMessage' => 'La surface doit être entre {{ min }} et {{ max }} m²',
                    ]),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: 75',
                ],
            ])
            ->add('occupants', IntegerType::class, [
                'label' => 'Nombre d\'occupants',
                'mapped' => false,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le nombre d\'occupants est obligatoire',
                    ]),
                    new Assert\Positive([
                        'message' => 'Le nombre d\'occupants doit être positif',
                    ]),
                    new Assert\Range([
                        'min' => 1,
                        'max' => 20,
                        'notInRangeMessage' => 'Le nombre d\'occupants doit être entre {{ min }} et {{ max }}',
                    ]),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: 3',
                ],
            ])

            // Données de consommation
            ->add('past_consumption', IntegerType::class, [
                'label' => 'Consommation passée (kWh)',
                'mapped' => false,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'La consommation est obligatoire',
                    ]),
                    new Assert\PositiveOrZero([
                        'message' => 'La consommation doit être un nombre positif ou zéro',
                    ]),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: 350',
                ],
            ])
            ->add('billing_date', DateType::class, [
                'label' => 'Date de facturation',
                'mapped' => false,
                'widget' => 'single_text',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'La date de facturation est obligatoire',
                    ]),
                    new Assert\LessThanOrEqual([
                        'value' => 'today',
                        'message' => 'La date ne peut pas être dans le futur',
                    ]),
                ],
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Pas de data_class car on gère 2 entités (Lodgment et Consumption)
        ]);
    }
}
