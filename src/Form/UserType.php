<?php

namespace App\Form;

use App\Entity\User;
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
            ->add('email', EmailType::class, [
                'required' => true,
                'constraints' => [
                    new Email(null, 'Not valid email')
                ]
            ])
            ->add('roles', ChoiceType::class, [
                'required' => false,
                'choices' => User::ROLES,
                'multiple' => true,
                'invalid_message' => 'Must be one of ' . implode(',', User::ROLES)
            ])
            ->add('password', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Regex('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[$@$!%*?&])[A-Za-z\d$@$!%*?&]{8,}/',
                        'Password must be min 8 characters and contain one uppercase, one lowercase, one digit and one character')
                ]
            ])
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
                        'Name must be 2 characters at least',
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
            'data_class' => User::class,
            'csrf_protection' => false,
        ]);
    }
}
