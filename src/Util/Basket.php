<?php

namespace App\Util;

use Symfony\Component\Cache\Adapter\AdapterInterface;
use Psr\Cache\InvalidArgumentException;

class Basket
{
    private AdapterInterface $cache;

    public function __construct(AdapterInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param string $cacheKey
     * @param array $basketItems
     * @return bool
     * @throws InvalidArgumentException
     */
    public function addBasket(string $cacheKey, array $basketItems): bool
    {
        $items = $this->cache->getItem($cacheKey);
        if (!$items->isHit()) {
            $items->set($basketItems);
            return $this->cache->save($items);
        }

        return true;
    }

    /**
     * @param string $cacheKey
     * @return array|null
     * @throws InvalidArgumentException
     */
    public function fetchBasket(string $cacheKey): ?array
    {
        $item = $this->cache->getItem($cacheKey);
        if (!$item->isHit()) {
            return null;
        }
        return $item->get();
    }


    /**
     * @param string $cacheKey
     * @return bool
     * @throws InvalidArgumentException
     */
    public function dropBasket(string $cacheKey): bool
    {
        return $this->cache->deleteItem($cacheKey);
    }
}