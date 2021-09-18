<?php

namespace App\Service;

interface DiscountInterface
{
    public function calculateDiscount(array $basketItems);
    public function removeDiscount(array $basketItems);

}