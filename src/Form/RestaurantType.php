<?php

namespace App\Form;

use App\Entity\Restaurant;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
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
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Url;
use Vich\UploaderBundle\Form\Type\VichImageType;

class RestaurantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $restaurant = $options['data'];

        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du restaurant',
                'attr' => ['placeholder' => 'Ex : Le Bistrot de Sophie'],
                'constraints' => [
                    new NotBlank(),
                ]
            ])
            ->add('siret', NumberType::class, [
                'label' => 'Numéro SIRET',
                'attr' => ['placeholder' => 'Ex : 12345678901234'],
                'constraints' => [
                    new NotBlank(),
                    new Regex([
                        'pattern' => '/^\d{14}$/',
                        'message' => 'Veuillez entrer un numéro SIRET valide.',
                    ])
                ]
            ])
            ->add('bannerFile', VichImageType::class, [
                'label' => 'Image de couverture',
                'required' => !$restaurant->getBannerName(),
                'download_uri' => false,
                'allow_delete' => false,
                'image_uri' => true,
                'constraints' => [
                    new Image([
                        'maxSize' => '2M',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                        'mimeTypesMessage' => 'Veuillez uploader une image valide (JPEG, PNG ou WebP)',
                        'maxSizeMessage' => 'L\'image ne doit pas dépasser 2 Mo.',
                    ])
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => [
                    'rows' => 5,
                    'placeholder' => 'Écrivez une description de votre restaurant ici...',
                ],
                'constraints' => [
                    new NotBlank(),
                ]
            ])
            ->add('address', TextType::class, [
                'label' => 'Adresse',
                'attr' => ['placeholder' => 'Ex : 123 rue du Marché'],
                'constraints' => [
                    new NotBlank(),
                ]
            ])
            ->add('postalCode', TextType::class, [
                'label' => 'Code Postal',
                'attr' => ['placeholder' => 'Ex : 33000'],
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
                'attr' => ['placeholder' => 'Ex : Bordeaux'],
                'constraints' => [
                    new NotBlank(),
                ]
            ])
            ->add('country', CountryType::class, [
                'label' => 'Pays',
                'placeholder' => 'Choisir un pays',
                'preferred_choices' => ['FR'],
                'data' => 'FR',
                'constraints' => [
                    new NotBlank(),
                ]
            ])
            ->add('phoneNumber', TelType::class, [
                'label' => 'Numéro de téléphone',
                'attr' => ['placeholder' => 'Ex : +33 6 12 34 56 78'],
                'constraints' => [
                    new Regex([
                        'pattern' => '/^\+?[0-9\s\-().]{7,}$/',
                        'message' => 'Veuillez entrer un numéro de téléphone valide.',
                    ])
                ],
            ])
            ->add('openingHours', TextareaType::class, [
                'label' => 'Horaires d’ouverture',
                'attr' => ['placeholder' => "Ex : Du mardi au samedi - 12h à 14h / 19h à 22h", 'rows' => 3],
                'constraints' => [
                    new NotBlank(),
                ]
            ])
            ->add('priceRange', ChoiceType::class, [
                'label' => 'Fourchette de prix',
                'choices' => [
                    '€' => '€',
                    '€€' => '€€',
                    '€€€' => '€€€',
                    '€€€€' => '€€€€',
                ],
                'expanded' => true,
                'constraints' => [
                    new NotBlank(),
                ]
            ])
            ->add('types', EntityType::class, [
                'label' => 'Types de cuisine',
                'class' => 'App\Entity\TypeRestaurant',
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('website', UrlType::class, [
                'label' => 'Site Web',
                'required' => false,
                'attr' => ['placeholder' => 'Ex : https://www.votre-restaurant.fr'],
                'constraints' => [
                    new Url([
                        'message' => 'Veuillez entrer une URL valide.',
                    ])
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Restaurant::class,
            'validation_groups' => function ($form) {
                return ['Default', 'FormSansCoords'];
            },
        ]);
    }
}
