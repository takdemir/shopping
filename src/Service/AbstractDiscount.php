<?php

namespace App\Service;

abstract class AbstractDiscount implements DiscountInterface
{
    /**
     * @param array $discountedBasketItems
     * @param array $basketItems
     * @param float $basketTotal
     * @param float $discountedTotal
     * @param float $discountAmount
     * @param string $discountCode
     * @return array
     */
    protected function setReturnData(
        array $discountedBasketItems,
        array $basketItems,
        float $basketTotal,
        float $discountedTotal,
        float $discountAmount,
        string $discountCode
    ): array
    {
        return [
            'items' => $discountedBasketItems,
            'basketTotal' => $basketTotal,
            'basketDiscountedTotal' => $discountedTotal,
            'discounts' => array_merge($basketItems['discounts'], [
                $discountCode => [
                    'discountReason' => $discountCode,
                    'discountAmount' => $discountAmount,
                    'discountedTotal' => $discountedTotal,
                ]
            ])
        ];
    }
}