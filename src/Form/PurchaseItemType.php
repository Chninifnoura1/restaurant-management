<?php

namespace App\Form;

use App\Entity\Ingredient;
use App\Entity\PurchaseItem;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PurchaseItemType extends AbstractType
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
                'label' => 'Quantité',
                'scale' => 3,
                'input' => 'string',
                'html5' => true,
                'attr' => ['min' => '0.001', 'step' => '0.001'],
            ])
            ->add('unitPrice', NumberType::class, [
                'label' => 'Prix unitaire',
                'scale' => 2,
                'input' => 'string',
                'html5' => true,
                'attr' => ['min' => '0', 'step' => '0.01'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PurchaseItem::class,
        ]);
    }
}
