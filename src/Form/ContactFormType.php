<?php

namespace App\Form;

use App\Entity\Contact;
use PixelOpen\CloudflareTurnstileBundle\Type\TurnstileType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType; 
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // On récupère la valeur envoyée par le Controller
        $isLoggedIn = $options['is_logged_in'];

        $builder
            ->add('email', null, [
                'attr' => [
                    'placeholder' => 'Votre e-mail',
                    'readonly' => $isLoggedIn, 
                ],
            ])
            ->add('nom', TextType::class, [
                'label' => 'Votre Nom',
                'attr' => [
                    'placeholder' => 'Votre nom',
                    // On peut aussi bloquer le nom si l'utilisateur est connecté
                    'readonly' => $isLoggedIn, 
                ],
            ])
            ->add('subject')
            ->add('message', TextareaType::class)
            ->add('security', TurnstileType::class, [
                'attr' => [
                    'data-action' => 'contact',
                    'data-theme' => 'light',
                ],
                'mapped' => false,
                'label' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Contact::class,
            'is_logged_in' => false, 
        ]);
    }
}