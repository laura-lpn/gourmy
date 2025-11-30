<?php

namespace App\Form;

use App\Entity\RestaurantCharter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RestaurantCharterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('usesLocalProducts', CheckboxType::class, [
                'label' => "J'utilise majoritairement des produits locaux ou régionaux",
                'required' => true,
            ])
            ->add('homemadeCuisine', CheckboxType::class, [
                'label' => "La plupart de mes plats sont faits maison",
                'required' => true,
            ])
            ->add('wasteReduction', CheckboxType::class, [
                'label' => "Je limite le gaspillage alimentaire et j'adopte des pratiques écoresponsables",
                'required' => true,
            ])
            ->add('transparentOrigin', CheckboxType::class, [
                'label' => "Je communique clairement l'origine des produits à mes clients",
                'required' => true,
            ])
            ->add('professionalRepliesToReviews', CheckboxType::class, [
                'label' => "Je m'engage à répondre de manière professionnelle aux avis laissés sur Gourmy",
                'required' => true,
            ])
            ->add('acceptsModeration', CheckboxType::class, [
                'label' => "J'accepte que ces informations soient vérifiées par les modérateurs Gourmy",
                'required' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RestaurantCharter::class,
        ]);
    }
}