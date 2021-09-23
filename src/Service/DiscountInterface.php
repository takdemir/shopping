<?php

namespace App\Service;

use App\Entity\Discount;

interface DiscountInterface
{
    /**
     * @param array $basketItems
     * @param Discount $discount
     * @return array
     * @description Calculates the discounts of items in basket
     */
    public function calculateDiscount(array $basketItems, Discount $discount): array;

    /**
     * @param array $parameters
     * @return array
     */
    public function prepareDiscountParameters(array $parameters): array;
}