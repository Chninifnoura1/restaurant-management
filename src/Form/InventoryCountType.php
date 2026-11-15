<?php

namespace App\Form;

use App\Entity\Ingredient;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InventoryCountType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ): void {
        $builder
            ->add('ingredient', EntityType::class, [
                'class' => Ingredient::class,
                'choice_label' => 'name',
                'placeholder' => 'Choisir un ingredient',
                'label' => 'Ingredient',
            ])
            ->add('physicalQuantity', NumberType::class, [
                'label' => 'Quantite physique comptee',
                'scale' => 3,
                'input' => 'string',
                'html5' => true,
                'attr' => [
                    'min' => '0',
                    'step' => '0.001',
                    'placeholder' => 'Exemple : 12.500',
                ],
            ])
            ->add('reason', TextareaType::class, [
                'label' => 'Motif de l inventaire',
                'attr' => [
                    'rows' => 3,
                    'maxlength' => 150,
                    'placeholder' => 'Exemple : Inventaire hebdomadaire',
                ],
            ]);
    }

    public function configureOptions(
        OptionsResolver $resolver
    ): void {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}