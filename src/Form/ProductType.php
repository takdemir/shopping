<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Product;
use App\Util\FormTypeUtils;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, FormTypeUtils::textTypeOptions(2, 255))
            ->add('isActive', CheckboxType::class)
            ->add('price', TextType::class, FormTypeUtils::textTypeRegexOption('Price', "/^[+-]?([0-9]*[.])?[0-9]+$/"))
            ->add('description', TextareaType::class, [
                'required' => false
            ])
            ->add('currency', ChoiceType::class, FormTypeUtils::choiceTypeOptions(Product::CURRENCIES, true, false))
            ->add('stock', TextType::class, FormTypeUtils::textTypeRegexOption('Price', "/^\d+$/"))
            ->add('createdAt', DateTimeType::class, FormTypeUtils::dateTimeTypeOptions(false))
            ->add('category', EntityType::class, FormTypeUtils::entityTypeOptions(Category::class));


        $builder->addEventListener(FormEvents::SUBMIT, static function (FormEvent $event) {
            $product = $event->getData();
            $product->setPrice(number_format($product->getPrice(), 2, '.', ''));
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
            'csrf_protection' => false
        ]);
    }
}
