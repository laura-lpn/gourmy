<?php

namespace App\Form;

use App\Entity\Restaurant;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class CreateRestaurantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du restaurant',
                'constraints' => [
                    new NotBlank(),
                ]
            ])
            ->add('siret', TextType::class, [
                'label' => 'Numéro SIRET',
                'constraints' => [
                    new NotBlank(),
                    new Regex([
                        'pattern' => '/^\d{14}$/',
                        'message' => 'Veuillez entrer un numéro SIRET valide.',
                    ])
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('address', TextType::class, [
                'label' => 'Adresse',
                'constraints' => [
                    new NotBlank(),
                ]
            ])
            ->add('postalCode', TextType::class, [
                'label' => 'Code Postal',
                'constraints' => [
                    new NotBlank(),
                    new Regex([
                        'pattern' => '/^[0-9]{5}$/',
                        'message' => 'Veuillez entrer un code postal valide.',
                    ])
                ]
            ])
            ->add('city', TextType::class, [
                'label' => 'Ville',
                'constraints' => [
                    new NotBlank(),
                ]
            ])
            ->add('country', CountryType::class, [
                'label' => 'Pays',
                'constraints' => [
                    new NotBlank(),
                ]
            ])
            ->add('latitude', NumberType::class, [
                'label' => 'Latitude',
                'constraints' => [
                    new NotBlank(),
                    new Regex([
                        'pattern' => '/^[-+]?[0-9]*\.?[0-9]+$/',
                        'message' => 'Veuillez entrer une latitude valide.',
                    ])
                ]
            ])
            ->add('longitude', NumberType::class, [
                'label' => 'Longitude',
                'constraints' => [
                    new NotBlank(),
                    new Regex([
                        'pattern' => '/^[-+]?[0-9]*\.?[0-9]+$/',
                        'message' => 'Veuillez entrer une longitude valide.',
                    ])
                ]
            ])
            ->add('phoneNumber', TelType::class, [
                'label' => 'Numéro de téléphone',
            ])
            ->add('openingHours', TextareaType::class, [
                'label' => 'Horaires d’ouverture',
                'required' => false,
            ])
            ->add('priceRange', ChoiceType::class, [
                'label' => 'Fourchette de prix',
                'choices' => [
                    '€' => '€',
                    '€€' => '€€',
                    '€€€' => '€€€',
                    '€€€€' => '€€€€',
                ],
                'constraints' => [
                    new NotBlank(),
                ]
            ])
            ->add('website', UrlType::class, [
                'label' => 'Site Web',
                'required' => false,
                'constraints' => [
                    new Regex([
                        'pattern' => '/^(https?|ftp):\/\/[^\s/$.?#].[^\s]*$/i',
                        'message' => 'Veuillez entrer une URL valide.',
                    ])
                ]
            ])
            ->add('banner', TextType::class, [
                'label' => 'Bannière',
                'constraints' => [
                    new NotBlank(),
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Restaurant::class,
        ]);
    }
}
