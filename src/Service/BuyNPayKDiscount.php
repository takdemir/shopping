<?php

namespace App\Service;

use App\Entity\Discount;

class BuyNPayKDiscount implements DiscountInterface
{

    public function calculateDiscount(array $basketItems, Discount $discount): array
    {
        return [
            'items' => [],
            'basketTotal' => 0,
            'basketDiscountedTotal' => 0,
            'discounts' => [
                'discountReason' => $discount->getDiscountCode(),
                'discountAmount' => 0,
                'discountedTotal' => 0,
            ]];
    }

    public function removeDiscount(array $basketItems)
    {
        // TODO: Implement removeDiscount() method.
    }
}