<?php

namespace App\Form;

use App\Entity\DishIngredient;
use App\Entity\Ingredient;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DishIngredientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('ingredient', EntityType::class, [
                'class' => Ingredient::class,
                'choice_label' => 'name',
                'placeholder' => 'Choisir un ingrédient',
                'label' => 'Ingrédient',
            ])
            ->add('quantity', NumberType::class, [
                'label' => 'Quantité nécessaire',
                'scale' => 3,
                'html5' => true,
                'attr' => [
                    'min' => '0.001',
                    'step' => '0.001',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DishIngredient::class,
        ]);
    }
}
