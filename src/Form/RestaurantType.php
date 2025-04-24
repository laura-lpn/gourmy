<?php

namespace App\Form;

use App\Entity\Restaurant;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
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
            ->add('phoneNumber', TelType::class, [
                'label' => 'Numéro de téléphone',
                'constraints' => [
                    new Regex([
                        'pattern' => '/^\+?[0-9\s\-().]{7,}$/',
                        'message' => 'Veuillez entrer un numéro de téléphone valide.',
                    ])
                ],
            ])
            ->add('openingHours', TextareaType::class, [
                'label' => 'Horaires d’ouverture',
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
                'constraints' => [
                    new NotBlank(),
                ]
            ])
            ->add('website', UrlType::class, [
                'label' => 'Site Web',
                'required' => false,
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
