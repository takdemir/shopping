<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Product;
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
            ->add('name', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Length(
                        null,
                        2,
                        255,
                        null,
                        null,
                        null,
                        'Name must be 5 characters at least',
                        'Name can be 255 characters max.')
                ]
            ])
            ->add('isActive', CheckboxType::class)
            ->add('price', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Regex("/^[+-]?([0-9]*[.])?[0-9]+$/", "Price must be numeric and positive")
                ]
            ])
            ->add('description', TextareaType::class, [
                'required' => false
            ])
            ->add('currency', ChoiceType::class, [
                'required' => false,
                'choices' => Product::CURRENCIES,
                'invalid_message' => 'Currency must be one of ' . implode(',', Product::CURRENCIES)
            ])
            ->add('stock', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Regex("/^\d+$/", "Stock must be numeric and positive")
                ],
                'invalid_message' => 'Stock must be numeric and positive'
            ])
            ->add('createdAt', DateTimeType::class, [
                'required' => false,
                'date_widget' => 'single_text',
                'constraints' => [
                    new GreaterThanOrEqual("today UTC", null, "Created date must be greater than or equal today")
                ]
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->orderBy('u.name', 'ASC');
                },
                'choice_label' => 'name',
                'invalid_message' => 'Category is not valid'
            ]);


        $builder->addEventListener(FormEvents::SUBMIT, static function (FormEvent $event) {
            $product = $event->getData();
            $product->setPrice(number_format($product->getPrice(), 2));
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
