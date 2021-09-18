<?php

namespace App\Service;

use App\Entity\Discount;

class PercentOverDiscount implements DiscountInterface
{

    public function calculateDiscount(array $basketItems, Discount $discount): array
    {
        $discountedBasketItems = $basketItems['items'];
        $basketTotal = $basketItems['basketTotal'];
        $discountAmount = 0;
        $discountedTotal = 0;

        if ((float)$basketItems['basketTotal'] >= 1000) {
            $discountAmount = $basketTotal * 0.1;
            $discountedTotal = $basketTotal - $discountAmount;
        }
        return [
            'items' => $discountedBasketItems,
            'basketTotal' => $basketTotal,
            'basketDiscountedTotal' => number_format($discountedTotal, 2, ',', ''),
            'discounts' => [
                'discountReason' => $discount->getDiscountCode(),
                'discountAmount' => number_format($discountAmount, 2, ',', ''),
                'discountedTotal' => number_format($discountedTotal, 2, ',', ''),
            ]];
    }

    public function removeDiscount(array $basketItems)
    {
        // TODO: Implement removeDiscount() method.
    }
}