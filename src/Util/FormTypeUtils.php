<?php

namespace App\Util;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;

class FormTypeUtils
{
    /**
     * @param int $min
     * @param int $max
     * @param bool $required
     * @return array
     */
    public static function textTypeOptions(int $min = 2, int $max = 50, bool $required = true): array
    {
        return [
            'required' => $required,
            'constraints' => [
                new Length(null,
                    $min,
                    $max,
                    null,
                    null,
                    null,
                    'Min. ' . $min . ' characters at least',
                    'Max. ' . $max . ' characters'
                )
            ]
        ];
    }

    /**
     * @param string $verifyItemName
     * @param string $regex
     * @param bool $required
     * @return array
     */
    public static function textTypeRegexOption(string $verifyItemName, string $regex, bool $required = true): array
    {
        return [
            'required' => $required,
            'constraints' => [
                new Regex($regex,
                    $verifyItemName . ' is not valid')
            ]
        ];
    }

    /**
     * @param bool $required
     * @return array
     */
    public static function emailTypeOptions(bool $required = true): array
    {
        return [
            'required' => $required,
            'constraints' => [
                new Email(null, 'Email is not valid')
            ]
        ];
    }

    /**
     * @param bool $required
     * @return array
     */
    public static function passwordTypeOptions(bool $required = true): array
    {
        return [
            'required' => $required,
            'constraints' => [
                new Regex('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[$@$!%*?&])[A-Za-z\d$@$!%*?&]{8,}/',
                    'Password must be min 8 characters and contain one uppercase, one lowercase, one digit and one character')
            ]
        ];
    }

    /**
     * @param array $choices
     * @param bool $required
     * @param bool $multiple
     * @return array
     */
    public static function choiceTypeOptions(array $choices, bool $required = true, bool $multiple = true): array
    {
        return [
            'required' => $required,
            'choices' => $choices,
            'multiple' => $multiple,
            'invalid_message' => 'Must be one of ' . implode(',', $choices)
        ];
    }

    /**
     * @param bool $required
     * @return array
     */
    public static function dateTimeTypeOptions(bool $required = true): array
    {
        return [
            'required' => $required,
            'date_widget' => 'single_text',
            'constraints' => [
                new GreaterThanOrEqual("today UTC", null, "Date must be greater than or equal today")
            ]
        ];
    }


    /**
     * @param string $entityClassName
     * @param string $entityFieldToShowInSelectBox
     * @param bool $required
     * @return array
     */
    public static function entityTypeOptions(string $entityClassName, string $entityFieldToShowInSelectBox = 'name', bool $required = true): array
    {
        return [
            'required' => $required,
            'class' => $entityClassName,
            'query_builder' => function (EntityRepository $er) use ($entityFieldToShowInSelectBox) {
                return $er->createQueryBuilder('u')
                    ->orderBy('u.' . $entityFieldToShowInSelectBox, 'ASC');
            },
            'choice_label' => $entityFieldToShowInSelectBox,
            'invalid_message' => 'Value is not valid'
        ];
    }

    /**
     * @param string $entryType
     * @param bool $required
     * @param bool $allowAdd
     * @return array
     */
    public static function collectionTypeOptions(string $entryType, bool $required = false, bool $allowAdd = true): array
    {
        return [
            'entry_type' => $entryType,
            'required' => $required,
            'allow_add' => $allowAdd
        ];
    }


}