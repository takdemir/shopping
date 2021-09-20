<?php

namespace App\Service;

use App\Entity\Discount;

class BuyNPayKDiscount extends AbstractDiscount
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
        $parameters = ['buy' => 6, 'pay' => 5, 'free' => 1, 'categories' => [2], 'products' => []];

        $isAnyItemExist = 0;

        foreach ($basketItems['items'] as $item) {
            if (in_array($item['categoryId'], $parameters['categories']) || in_array($item['productId'], $parameters['products'])) {
                if ($item['quantity'] === $parameters['buy']) {
                    $isAnyItemExist++;
                    $discountAmount = $parameters['free'] * (float)$item['unitPrice'];
                    $discountedTotal = $basketDiscountedTotal - (float)$discountAmount;
                }
            }
        }
        if ($isAnyItemExist === 0) {
            return $basketItems;
        }
        return $this->setReturnData($discountedBasketItems, $basketItems, $basketTotal, $discountedTotal, $discountAmount, $discount->getDiscountCode());
    }

    public function removeDiscount(array $basketItems)
    {
        // TODO: Implement removeDiscount() method.
    }
}