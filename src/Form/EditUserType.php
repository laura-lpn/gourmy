<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotCompromisedPassword;
use Symfony\Component\Validator\Constraints\Regex;
use Vich\UploaderBundle\Form\Type\VichImageType;

class EditUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $options['data'];
        $builder
            ->add('username', null, [
                'label' => 'Nom d\'utilisateur',
                'constraints' => [
                    new Length([
                        'min' => 3,
                        'minMessage' => 'Le nom d\'utilisateur doit contenir au moins {{ limit }} caractères',
                        'max' => 20,
                        'maxMessage' => 'Le nom d\'utilisateur ne doit pas dépasser {{ limit }} caractères',
                    ]),
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => 'Adresse email',
                'attr' => ['autocomplete' => 'email'],
            ])
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
                'attr' => ['autocomplete' => 'given-name'],
                'constraints' => [
                    new Length([
                        'max' => 50,
                        'maxMessage' => 'Le prénom ne doit pas dépasser {{ limit }} caractères',
                    ]),
                ]
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
                'attr' => ['autocomplete' => 'family-name'],
                'constraints' => [
                    new Length([
                        'max' => 50,
                        'maxMessage' => 'Le nom ne doit pas dépasser {{ limit }} caractères',
                    ]),
                ]
            ])            
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Mot de passe',
                'mapped' => false,
                'required' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new Length([
                        'min' => 8,
                        'minMessage' => 'Votre mot de passe doit contenir au moins {{ limit }} caractères',
                        'max' => 64,
                        'maxMessage' => 'Votre mot de passe ne doit pas dépasser {{ limit }} caractères',
                    ]),
                    new Regex([
                        'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/',
                        'message' => 'Le mot de passe doit contenir au moins une lettre majuscule, une lettre minuscule, un chiffre et un caractère spécial.',
                    ]),
                    new NotCompromisedPassword([
                        'message' => 'Le mot de passe a été compromis dans une fuite de données, veuillez en choisir un autre.',
                    ]),
                ],
            ])
            ->add('avatarFile', VichImageType::class, [
                'label' => 'Image de profil',
                'required' => !$user->getAvatarName(),
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
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
