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
        $parameters = $this->prepareDiscountParameters($discount->getParameters());
        if (!$parameters) {
            return $basketItems;
        }

        $isAnyItemExist = 0;
        foreach ($basketItems['items'] as $item) {
            if (in_array($item['categoryId'], $parameters['categories']) || in_array($item['productId'], $parameters['products'])) {
                if ($item['quantity'] === $parameters['buy']) {
                    $isAnyItemExist++;
                    $discountAmount = (int)$parameters['free'] * (float)$item['unitPrice'];
                    $discountedTotal = $basketDiscountedTotal - (float)$discountAmount;
                }
            }
        }
        if ($isAnyItemExist === 0) {
            return $basketItems;
        }
        return $this->setReturnData($discountedBasketItems, $basketItems, $basketTotal, $discountedTotal, $discountAmount, $discount->getDiscountCode());
    }

    /**
     * @param array $parameters
     * @return array
     */
    public function prepareDiscountParameters(array $parameters): array
    {
        $convertedParameters = [];
        foreach ($parameters as $key => $parameter) {
            switch ($key) {
                case "buy":
                case "free":
                case "pay":
                    $convertedParameters[$key] = (int)$parameter;
                    break;
                case "products":
                case "categories":
                    $convertedParameters[$key] = json_decode($parameter, true);
                    break;
            }
        }
        return $convertedParameters;
    }
}