<?php

namespace App\Service;

use App\Entity\Discount;

class BuyNDecreasePercentDiscount extends AbstractDiscount
{
    public function calculateDiscount(array $basketItems, Discount $discount): array
    {
        //If that discount is implemented before, remove it to calculate again
        if (array_key_exists($discount->getDiscountCode(), $basketItems['discounts'])) {
            unset($basketItems['discounts'][$discount->getDiscountCode()]);
        }
        $discountedBasketItems = $basketItems['items'];
        $basketTotal = (float)$basketItems['basketTotal'];
        $basketDiscountedTotal = $discountedTotal = (float)$basketItems['basketDiscountedTotal'];
        $discountAmount = 0;

        //TODO: get this parameters from DB
        $parameters = ['buy' => 2, 'discountPercent' => 20, 'categories' => [1], 'products' => []];

        // Find the cheapest item in the basket
        $cheapestItem = array_reduce($basketItems['items'], static function ($previousItem, $currentItem) {
            if (is_null($previousItem)) {
                return $currentItem;
            }
            if ((float)$currentItem['total'] < (float)$previousItem['total']) {
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

        return $this->setReturnData($discountedBasketItems, $basketItems, $basketTotal, $discountedTotal, $discountAmount, $discount->getDiscountCode());
    }

    public function removeDiscount(array $basketItems)
    {
        // TODO: Implement removeDiscount() method.
    }
}