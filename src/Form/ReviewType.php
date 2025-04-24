<?php

namespace App\Form;

use App\Entity\Review;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Range;
use Vich\UploaderBundle\Form\Type\VichImageType;

class ReviewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isReply = $options['is_reply'] ?? false;

        $builder
            ->add('comment', CKEditorType::class, [
                'label' => 'Commentaire',
                'required' => true,
                'config' => [
                    'toolbar' => 'basic',
                ],
                'attr' => [
                    'rows' => 5,
                    'placeholder' => 'Écrivez votre commentaire ici...',
                ]
            ]);

        if (!$isReply) {
            $builder
                ->add('title', TextType::class, [
                    'required' => false,
                    'label' => 'Titre',
                    'attr' => [
                        'placeholder' => 'Titre de votre avis',
                    ]
                ])
                ->add('rating', IntegerType::class, [
                    'required' => true,
                    'attr' => ['min' => 0, 'max' => 5],
                    'constraints' => [
                        new Range(
                            [
                                'min' => 0,
                                'max' => 5,
                                'notInRangeMessage' => 'La note doit être comprise entre {{ min }} et {{ max }}.',
                            ]
                        )
                    ]
                ])
                ->add('imageFile', VichImageType::class, [
                    'label' => 'Image (optionelle)',
                    'required' => false,
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
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Review::class,
            'csrf_protection' => true,
            'is_reply' => false,
        ]);
    }
}
