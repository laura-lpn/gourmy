<?php

namespace App\Form;

use App\Entity\Restaurant;
use App\Entity\Review;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReviewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('comment')
            ->add('rating')
            ->add('imageName')
            ->add('imageUpdatedAt', null, [
                'widget' => 'single_text',
            ])
            ->add('uuid')
            ->add('createdAt', null, [
                'widget' => 'single_text',
            ])
            ->add('updatedAt', null, [
                'widget' => 'single_text',
            ])
            ->add('restaurant', EntityType::class, [
                'class' => Restaurant::class,
'choice_label' => 'id',
            ])
            ->add('author', EntityType::class, [
                'class' => User::class,
'choice_label' => 'id',
            ])
            ->add('response', EntityType::class, [
                'class' => Review::class,
'choice_label' => 'id',
            ])
            ->add('originalReview', EntityType::class, [
                'class' => Review::class,
'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Review::class,
        ]);
    }
}
