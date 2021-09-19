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
            'basketDiscountedTotal' => number_format($discountedTotal, 2, ',', ''),
            'discounts' => array_merge($basketItems['discounts'], [
                $discountCode => [
                    'discountReason' => $discountCode,
                    'discountAmount' => number_format($discountAmount, 2, ',', ''),
                    'discountedTotal' => number_format($discountedTotal, 2, ',', ''),
                ]
            ])
        ];
    }
}