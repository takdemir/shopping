<?php

namespace App\Service;

use App\Entity\Discount;

interface DiscountInterface
{
    public function calculateDiscount(array $basketItems, Discount $discount): array;

    public function removeDiscount(array $basketItems);

}