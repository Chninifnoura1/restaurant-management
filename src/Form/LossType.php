<?php

namespace App\Form;

use App\Entity\Ingredient;
use App\Entity\Loss;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LossType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ): void {
        $builder
            ->add('ingredient', EntityType::class, [
                'class' => Ingredient::class,
                'choice_label' => 'name',
                'placeholder' => 'Choisir un ingrédient',
                'label' => 'Ingrédient',
            ])
            ->add('quantity', NumberType::class, [
                'label' => 'Quantité perdue',
                'scale' => 3,
                'input' => 'string',
                'html5' => true,
                'attr' => [
                    'min' => '0.001',
                    'step' => '0.001',
                    'placeholder' => 'Exemple : 2.500',
                ],
            ])
            ->add('reason', TextareaType::class, [
                'label' => 'Motif de la perte',
                'attr' => [
                    'rows' => 3,
                    'placeholder' => 'Exemple : Produit périmé, casse, erreur en cuisine...',
                ],
            ]);
    }

    public function configureOptions(
        OptionsResolver $resolver
    ): void {
        $resolver->setDefaults([
            'data_class' => Loss::class,
        ]);
    }
}
