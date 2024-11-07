<?php

namespace App\Form;

use App\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class)
            ->add('description')
            ->add('price', MoneyType::class, [
                'currency' => null,  // Removes the currency symbol
                'attr' => ['class' => 'form-control', 'step' => '0.01'],
            ])
            ->add('stock_quantity');
            // ->add('created_at', DateTimeType::class, [
            //     'widget' => 'single_text',
            //     'disabled' => true,  // Automatically set
            // ])
            // ->add('updated_at', DateTimeType::class, [
            //     'widget' => 'single_text',
            //     'disabled' => true,  
            // ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
