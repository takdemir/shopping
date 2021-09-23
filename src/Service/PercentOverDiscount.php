<?php

namespace App\Service;

use App\Entity\Discount;

class PercentOverDiscount extends AbstractDiscount
{
    public function calculateDiscount(array $basketItems, Discount $discount): array
    {
        //If that discount is implemented before, remove it to calculate again
        if (array_key_exists($discount->getDiscountCode(), $basketItems['discounts'])) {
            unset($basketItems['discounts'][$discount->getDiscountCode()]);
        }
        $discountedBasketItems = $basketItems['items'];
        $basketTotal = (float)$basketItems['basketTotal'];
        $basketDiscountedTotal = (float)$basketItems['basketDiscountedTotal'];

        //TODO: get this parameters from DB
        $parameters = ['basketTotalForDiscount' => 1000, 'discountPercent' => 10];

        // Because of another discount is implemented before that discount, I get $basketDiscountedTotal value to calculate
        if ((float)$basketItems['basketTotal'] < $parameters['basketTotalForDiscount']) {
            return $basketItems;
        }
        $discountAmount = $basketDiscountedTotal * ($parameters['discountPercent'] / 100);
        $discountedTotal = $basketDiscountedTotal - $discountAmount;

        return $this->setReturnData($discountedBasketItems, $basketItems, $basketTotal, $discountedTotal, $discountAmount, $discount->getDiscountCode());
    }

    public function removeDiscount(array $basketItems)
    {
        // TODO: Implement removeDiscount() method.
    }
}