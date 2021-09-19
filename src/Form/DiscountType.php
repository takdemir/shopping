<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Discount;
use App\Entity\Product;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class DiscountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('discountCode', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Length(
                        null,
                        5,
                        50,
                        null,
                        null,
                        null,
                        'Discount code must be 5 characters at least',
                        'Discount code can be 255 characters max.')
                ]
            ])
            ->add('discountClassName', ChoiceType::class, [
                'required' => true,
                'choices' => Discount::DISCOUNT_CLASS_NAMES,
                'invalid_message' => 'Discount classname can be one of ' . implode(',', Discount::DISCOUNT_CLASS_NAMES)
            ])
            ->add('isActive', CheckboxType::class)
            ->add('parameters', CollectionType::class, [
                'entry_type' => TextType::class,
                'required' => false,
                'allow_add' => true
            ])
            ->add('createdAt', DateTimeType::class, [
                'required' => false,
                'date_widget' => 'single_text',
                'constraints' => [
                    new GreaterThanOrEqual("today UTC", null, "Created date must be greater than or equal today")
                ]
            ])
            ->add('startAt', DateTimeType::class, [
                'required' => false,
                'date_widget' => 'single_text',
                'constraints' => [
                    new GreaterThanOrEqual("today UTC", null, "Start date must be greater than or equal today")
                ]
            ])
            ->add('expireAt', DateTimeType::class, [
                'required' => false,
                'date_widget' => 'single_text',
                'constraints' => [
                    new GreaterThanOrEqual("today UTC", null, "Expire date must be greater than or equal today")
                ]
            ])
            ->add('user', EntityType::class, [
                'required' => false,
                'class' => User::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->orderBy('u.name', 'ASC');
                },
                'choice_label' => 'name',
                'invalid_message' => 'User is not valid'
            ])
            ->add('category', EntityType::class, [
                'required' => false,
                'class' => Category::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->orderBy('u.name', 'ASC');
                },
                'choice_label' => 'name',
                'invalid_message' => 'Category is not valid'
            ])
            ->add('product', EntityType::class, [
                'required' => false,
                'class' => Product::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->orderBy('u.name', 'ASC');
                },
                'choice_label' => 'name',
                'invalid_message' => 'Product is not valid'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Discount::class,
            'constraints' => [
                new Callback([$this, 'validate']),
            ],
            'csrf_protection' => false
        ]);
    }

    public function validate($payload, ExecutionContextInterface $context): void
    {
        /*if ($payload->getStartAt() > $payload->getExpireAt()) {
            $context->buildViolation('Start date must be earlier than expire date')
                ->atPath('startAt')
                ->addViolation();
        }*/
    }
}
