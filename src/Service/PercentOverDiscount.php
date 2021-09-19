<?php

namespace App\Service;

use App\Entity\Discount;

class PercentOverDiscount implements DiscountInterface
{

    public function calculateDiscount(array $basketItems, Discount $discount): array
    {
        //If that discount is implemented before, don't implement again
        if (array_key_exists($discount->getDiscountCode(), $basketItems['discounts'])) {
            return $basketItems;
        }
        $discountedBasketItems = $basketItems['items'];
        $basketTotal = $basketItems['basketTotal'];
        $basketDiscountedTotal = $basketItems['basketDiscountedTotal'];
        $discountAmount = 0;
        $discountedTotal = 0;

        //TODO: get this parameters from DB
        $parameters = ['basketTotalForDiscount' => 1000, 'discountPercent' => 10];

        // Because of another discount is implemented before that discount, I get $basketDiscountedTotal value to calculate
        if ((float)$basketItems['basketTotal'] >= $parameters['basketTotalForDiscount']) {
            $discountAmount = $basketDiscountedTotal * ($parameters['discountPercent'] / 100);
            $discountedTotal = $basketDiscountedTotal - $discountAmount;
        }
        return [
            'items' => $discountedBasketItems,
            'basketTotal' => $basketTotal,
            'basketDiscountedTotal' => number_format($discountedTotal, 2, ',', ''),
            'discounts' => array_merge($basketItems['discounts'], [
                $discount->getDiscountCode() => [
                    'discountReason' => $discount->getDiscountCode(),
                    'discountAmount' => number_format($discountAmount, 2, ',', ''),
                    'discountedTotal' => number_format($discountedTotal, 2, ',', ''),
                ]
            ])
        ];
    }

    public function removeDiscount(array $basketItems)
    {
        // TODO: Implement removeDiscount() method.
    }
}