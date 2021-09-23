<?php

namespace App\Form;

use App\Entity\User;
use App\Util\FormTypeUtils;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, FormTypeUtils::emailTypeOptions())
            ->add('roles', ChoiceType::class, FormTypeUtils::choiceTypeOptions(User::ROLES, false))
            ->add('password', TextType::class, FormTypeUtils::passwordTypeOptions(true))
            ->add('name', TextType::class, FormTypeUtils::textTypeOptions(2, 255))
            ->add('isActive', CheckboxType::class)
            ->add('createdAt', DateTimeType::class, FormTypeUtils::dateTimeTypeOptions(false));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'csrf_protection' => false,
        ]);
    }
}
