<?php

namespace App\Form;

use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\Length;

class CategoryType extends AbstractType
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
            ->add('createdAt', DateTimeType::class, [
                'required' => false,
                'date_widget' => 'single_text',
                'constraints' => [
                    new GreaterThanOrEqual("today UTC", null, "Created date must be greater than or equal today")
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Category::class,
            'csrf_protection' => false,
        ]);
    }
}
