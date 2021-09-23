<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Discount;
use App\Entity\Product;
use App\Entity\User;
use App\Util\FormTypeUtils;
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
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class DiscountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('discountCode', TextType::class, FormTypeUtils::textTypeOptions(5, 50))
            ->add('discountClassName', ChoiceType::class, FormTypeUtils::choiceTypeOptions(Discount::DISCOUNT_CLASS_NAMES, true, false))
            ->add('isActive', CheckboxType::class)
            ->add('parameters', CollectionType::class, FormTypeUtils::collectionTypeOptions(TextType::class))
            ->add('createdAt', DateTimeType::class, FormTypeUtils::dateTimeTypeOptions(false))
            ->add('startAt', DateTimeType::class, FormTypeUtils::dateTimeTypeOptions(false))
            ->add('expireAt', DateTimeType::class, FormTypeUtils::dateTimeTypeOptions(false))
            ->add('user', EntityType::class, FormTypeUtils::entityTypeOptions(User::class, 'name', false))
            ->add('category', EntityType::class, FormTypeUtils::entityTypeOptions(Category::class, 'name', false))
            ->add('product', EntityType::class, FormTypeUtils::entityTypeOptions(Product::class, 'name', false));
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
        try {
            if ($payload->getStartAt() > $payload->getExpireAt()) {
                $context->buildViolation('Start date must be earlier than expire date')
                    ->atPath('startAt')
                    ->addViolation();
            }
        } catch (\Exception $exception) {
            //TODO: Log
        }

    }
}
