<?php

namespace App\Service;

use App\Entity\Discount;

class BuyNDecreasePercentDiscount implements DiscountInterface
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
        $parameters = ['buy' => 2, 'discountPercent' => 20, 'categories' => [1], 'products' => []];

        // Find the cheapest item in the basket
        $cheapestItem = array_reduce($basketItems['items'], static function ($previousItem, $currentItem) {
            if (is_null($previousItem)) {
                return $currentItem;
            }
            if ($currentItem['total'] < $previousItem['total']) {
                return $currentItem;
            }
        });

        // Search in the items if category or product exist. If so, implement the related discount
        foreach ($basketItems['items'] as $item) {
            if (in_array($item['categoryId'], $parameters['categories']) || in_array($item['productId'], $parameters['products'])) {
                if ($item['quantity'] >= $parameters['buy']) {
                    $discountAmount = ($parameters['discountPercent'] / 100) * (float)$cheapestItem['total'];
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