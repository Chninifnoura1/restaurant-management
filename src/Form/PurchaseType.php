<?php

namespace App\Form;

use App\Entity\Purchase;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PurchaseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('supplierName', TextType::class, [
                'label' => 'Nom du fournisseur',
            ])
            ->add('purchaseDate', DateTimeType::class, [
                'label' => "Date d'achat",
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
            ])
            ->add('reference', TextType::class, [
                'label' => 'Référence',
                'required' => false,
            ])
            ->add('purchaseItems', CollectionType::class, [
                'entry_type' => PurchaseItemType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
                'label' => 'Ingrédients achetés',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Purchase::class,
        ]);
    }
}
