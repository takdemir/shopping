<?php

namespace App\Service;

use App\Entity\Discount;

class BuyNPayKDiscount implements DiscountInterface
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
        $parameters = ['buy' => 6, 'pay' => 5, 'free' => 1, 'categories' => [2], 'products' => []];

        foreach ($basketItems['items'] as $item) {
            if (in_array($item['categoryId'], $parameters['categories']) || in_array($item['productId'], $parameters['products'])) {
                if ($item['quantity'] === $parameters['buy']) {
                    $discountAmount = $parameters['free'] * $item['unitPrice'];
                    $discountedTotal = $basketDiscountedTotal - $discountAmount;
                }
            }
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