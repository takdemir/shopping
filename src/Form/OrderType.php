<?php

namespace App\Form;

use App\Entity\Order;
use App\Entity\User;
use App\Util\FormTypeUtils;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class OrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('total', TextType::class, FormTypeUtils::textTypeRegexOption('Total', "/^[+-]?([0-9]*[.])?[0-9]+$/"))
            ->add('createdAt', DateTimeType::class, FormTypeUtils::dateTimeTypeOptions(false))
            ->add('user', EntityType::class, FormTypeUtils::entityTypeOptions(User::class));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
            'csrf_protection' => false
        ]);
    }
}
