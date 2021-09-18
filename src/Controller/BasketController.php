<?php

namespace App\Controller;

use App\Entity\Discount;
use App\Entity\Product;
use App\Service\DiscountInterface;
use App\Service\PercentOverDiscount;
use App\Service\BuyNDecreasePercentDiscount;
use App\Service\BuyNPayKDiscount;
use App\Util\ReplyUtils;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Cache\InvalidArgumentException;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Security as AnnotationSecurity;

/**
 * Class BasketController
 * @package App\Controller
 * @Route("/api/v1/basket", name="api_v1_basket_", schemes={"https","http"})
 * @Security("is_granted('ROLE_USER')")
 */
class BasketController extends BaseController
{
    use BaseTrait;

    /**
     * @Route("", name="add", methods={"POST"})
     * @throws InvalidArgumentException
     * @OA\Response (
     *     response="200",
     *     description="Add items to basket",
     *     @OA\JsonContent(
     *           @OA\Property(property="status", type="boolean"),
     *           @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *           @OA\Property(property="message", type="string"),
     *        )
     * )
     * @OA\RequestBody (
     *     description="",
     *     required=true,
     *     @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema (
     *                  type="object",
     *                  @OA\Property (
     *                          property="items",
     *                          description="Basket items",
     *                          type="array",
     *                          @OA\Items(type="object",
     *                                      @OA\Property (property="product", description="Product Id", type="integer"),
     *                                      @OA\Property (property="quantity", description="Quantity", type="integer"),
     *                          )
     *                  ),
     *                  @OA\Property (
     *                          property="productIds",
     *                          description="Products IDs in array",
     *                          type="array",
     *                          @OA\Items(type="integer")
     *                  )
     *           )
     *      )
     * )
     * @OA\Tag(name="Basket")
     * @AnnotationSecurity(name="Authorization")
     */
    public function add(Request $request): JsonResponse
    {
        if (!$this->checkContentType($request->headers->get('content-type'))) {
            return $this->json(ReplyUtils::failure(['message' => 'Content-type must be application/json!']));
        }

        $postedData = json_decode($request->getContent(), true);

        if (!$user = $this->getUser()) {
            return $this->json(ReplyUtils::failure(['message' => 'No user found!']), 403);
        }

        if (!array_key_exists('items', $postedData) || !$postedData['items']) {
            return $this->json(ReplyUtils::success(['message' => 'No items found in the basket!']));
        }

        //  I extra sent product IDs not to loop in items to get product IDs,
        if (!array_key_exists('productIds', $postedData) || !$postedData['productIds']) {
            return $this->json(ReplyUtils::success(['message' => 'No productIds found in the basket!']));
        }

        $cacheKey = md5($user->getUserIdentifier());
        $basketItems = $postedData['items'];

        //Fetch all products in basket at the same time not to get them from repo one by one
        $productRepository = $this->em->getRepository(Product::class);
        $products = $productRepository->fetchProductsByIds($postedData['productIds']);

        $noValidDataInBasketItems = [];
        $noStockProducts = [];

        // I set new array which keys are productId. I fetch all products detail at one time from DB and I will get product info without loop
        $rePreparedProducts = [];
        foreach ($products as $product) {
            $rePreparedProducts[$product['id']] = $product;
        }

        foreach ($basketItems as $basketItem) {
            if (!array_key_exists('product', $basketItem) || !$basketItem['product']) {
                $noValidDataInBasketItems[] = $basketItem;
                break;
            }
            if (!array_key_exists('quantity', $basketItem) || $basketItem['quantity'] === 0 || !is_int((int)$basketItem['quantity'])) {
                $noValidDataInBasketItems[] = $basketItem;
                break;
            }
            $product = $rePreparedProducts[$basketItem['product']];
            if ($basketItem['quantity'] > $product['stock']) {
                $noStockProducts[] = $product['name'];
            }
        }

        if ($noValidDataInBasketItems) {
            return $this->json(ReplyUtils::failure(['message' => 'All items objects must contain product and quantity info. Please check it!']));
        }

        if ($noStockProducts) {
            return $this->json(ReplyUtils::failure(['message' => 'No enough stock for ' . implode(',', $noStockProducts)]));
        }

        $fetchBasketFromCache = $this->cacheUtil->fetch($cacheKey);

        $itemsWillBeCached = [];

        // There may be a customer cached basket. That is why, I check the data in cache. If so,  I will merge the new basket data to cached file, else I will add it to basket as a new item

        $alreadyAddedProducts = [];

        if ($fetchBasketFromCache && array_key_exists('items', $fetchBasketFromCache)) {
            $items = $fetchBasketFromCache['items'];
            foreach ($basketItems as $basketItem) {
                $product = $rePreparedProducts[$basketItem['product']];
                foreach ($items as $key => $cachedItem) {
                    if ($cachedItem['productId'] === $basketItem['product']) {
                        $newQuantity = $cachedItem['quantity'] + $basketItem['quantity'];
                        // Let's check the new quantity again for stock. If no enough stock, I will warn the customer.
                        if ($newQuantity > $product['stock']) {
                            $noStockProducts[] = $product['name'];
                            $newQuantity = ($cachedItem['quantity'] < $product['stock']) ? $cachedItem['quantity'] : $product['stock'];
                            if ($newQuantity === 0) {
                                unset($items[$key]);
                                continue;
                            }
                        }
                        $alreadyAddedProducts[] = $basketItem['product'];
                        $cachedItem['quantity'] = $newQuantity;
                        // Unit price may be changed. So I get the unitPrice again from $product.
                        $cachedItem['total'] = number_format($cachedItem['quantity'] * $product['price'], 2, '.', '');
                        $itemsWillBeCached[] = $cachedItem;
                    }
                }
            }
        }
        $newCachedItems = array_merge($itemsWillBeCached, $this->setItemsWillBeCached($basketItems, $alreadyAddedProducts, $rePreparedProducts));

        $basketTotal = 0;
        if ($newCachedItems) {
            $basketTotal = $this->calculateBasketTotal($newCachedItems);
        }

        $cacheData = [
            'items' => $newCachedItems,
            'basketTotal' => $basketTotal,
            'basketDiscountedTotal' => $basketTotal,
            'discounts' => []
        ];

        $discountResult = $this->calculateDiscount($rePreparedProducts, $cacheData);
        if ($discountResult) {
            $cacheData = $discountResult;
        }

        dd($cacheData);

        $message = "success";
        if ($noStockProducts) {
            $message = 'No more stock for ' . implode(',', $noStockProducts) . '. So I couldn\'t increase their quantities.';
        }

        $cacheDropResult = $this->cacheUtil->drop($cacheKey);
        if (!$cacheDropResult) {
            return $this->json(ReplyUtils::failure(['data' => [], 'message' => 'An error has occurred while dropping the cache']));
        }
        $cacheAddResult = $this->cacheUtil->add($cacheKey, $cacheData);
        if (!$cacheAddResult) {
            return $this->json(ReplyUtils::failure(['data' => [], 'message' => 'An error has occurred while adding to basket cache']));
        }
        return $this->json(ReplyUtils::success(['data' => $cacheData, 'basketTotal' => $basketTotal, 'message' => $message]));
    }


    /**
     * @param array $rePreparedProducts
     * @param array $cacheData
     * @return array
     */
    private function calculateDiscount(array $rePreparedProducts, array $cacheData): array
    {
        $discountedCacheData = $cacheData;

        // Let's calculate the discounts. First, let's fetch all active, started and not expired discounts.
        $discountRepository = $this->em->getRepository(Discount::class);
        $fetchDiscounts = $discountRepository->findBy(['isActive' => true]);
        //dd($fetchDiscounts);
        if ($fetchDiscounts) {
            $now = new \DateTime();
            foreach ($fetchDiscounts as $discount) {

                //Let's check if any start date for discount. If so I will check if discount is started or not. If not, I will continue
                if (!is_null($discount->getStartAt()) && $discount->getStartAt() > $now) {
                    continue;
                }

                //Let's check if any expiry date for discount. If so I will check if discount has expired or not. If so, I will continue
                if (!is_null($discount->getExpireAt()) && $discount->getExpireAt() < $now) {
                    continue;
                }

                /**
                 * @var DiscountInterface $discountCalculator
                 */
                $className = $discount->getDiscountClassName();
                if (!$className) {
                    continue;
                }

                switch ($className) {
                    case 'PercentOverDiscount':
                        $discountCalculator = new PercentOverDiscount();
                        break;
                    case 'BuyNPayKDiscount':
                        $discountCalculator = new BuyNPayKDiscount();
                        break;
                    case 'BuyNDecreasePercentDiscount':
                        $discountCalculator = new BuyNDecreasePercentDiscount();
                        break;
                }

                if (($category = $discount->getCategory()) && !is_null($category)) {
                    foreach ($rePreparedProducts as $rePreparedProduct) {
                        if ($rePreparedProduct['category']['id'] !== $category->getId()) {
                            continue;
                        }
                    }
                } else {
                    $discountResult = $discountCalculator->calculateDiscount($cacheData, $discount);
                    $discountedCacheData = [
                        'items' => $discountResult['items'],
                        'basketTotal' => $cacheData['basketTotal'],
                        'basketDiscountedTotal' => $discountResult['basketDiscountedTotal'],
                        'discounts' => $discountResult['discounts']
                    ];

                }
            }
        }

        return $discountedCacheData;
    }

    /**
     * @Route("/remove", name="remove", methods={"DELETE"})
     * @throws InvalidArgumentException
     * @OA\Response (
     *     response="200",
     *     description="Remove items and returns the new basket content",
     *     @OA\JsonContent(
     *           @OA\Property(property="status", type="boolean"),
     *           @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *           @OA\Property(property="message", type="string"),
     *        )
     * ),
     * @OA\Parameter (
     *     name="product",
     *     in="query",
     *     description="Product ID",
     *     @OA\Schema (type="integer"),
     * ),
     * @OA\Tag(name="Basket")
     * @AnnotationSecurity(name="Authorization")
     */
    public function remove(Request $request): JsonResponse
    {
        if (!$this->checkContentType($request->headers->get('content-type'))) {
            return $this->json(ReplyUtils::failure(['message' => 'Content-type must be application/json!']));
        }

        $query = $request->query->all();

        if (!$user = $this->getUser()) {
            return $this->json(ReplyUtils::failure(['message' => 'No user found!']), 403);
        }

        if (!array_key_exists('product', $query) || !is_int((int)$query['product'])) {
            return $this->json(ReplyUtils::success(['message' => 'No product found to remove from basket!']));
        }
        $cacheKey = md5($user->getUserIdentifier());
        $fetchBasketFromCache = $this->cacheUtil->fetch($cacheKey);
        if (!$fetchBasketFromCache || !array_key_exists('items', $fetchBasketFromCache)) {
            return $this->json(ReplyUtils::success(['data' => [], 'basketTotal' => 0, 'message' => 'No items in basket']));
        }
        $items = $fetchBasketFromCache['items'];

        $newBasketItems = array_filter($items, static function ($item) use ($query) {
            return $item['productId'] !== (int)$query['product'];
        });

        $basketTotal = 0;
        if ($newBasketItems) {
            $basketTotal = $this->calculateBasketTotal($newBasketItems);
        }

        $cacheData = [
            'items' => $newBasketItems,
            'basketTotal' => $basketTotal,
            'basketDiscountedTotal' => $fetchBasketFromCache['basketDiscountedTotal'],
            'discounts' => $fetchBasketFromCache['discounts']
        ];

        $cacheDropResult = $this->cacheUtil->drop($cacheKey);
        if (!$cacheDropResult) {
            return $this->json(ReplyUtils::failure(['data' => [], 'message' => 'An error has occurred while dropping the cache']));
        }
        $cacheAddResult = $this->cacheUtil->add($cacheKey, $cacheData);
        if (!$cacheAddResult) {
            return $this->json(ReplyUtils::failure(['data' => [], 'message' => 'An error has occurred while adding to basket cache']));
        }

        return $this->json(ReplyUtils::success(['data' => $cacheData, 'basketTotal' => $basketTotal, 'message' => 'success']));
    }


    /**
     * @Route("", name="empty", methods={"DELETE"})
     * @throws InvalidArgumentException
     * @OA\Response (
     *     response="200",
     *     description="Remove all items from basket",
     *     @OA\JsonContent(
     *           @OA\Property(property="status", type="boolean"),
     *           @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *           @OA\Property(property="message", type="string"),
     *        )
     * ),
     * @OA\Tag(name="Basket")
     * @AnnotationSecurity(name="Authorization")
     */
    public function empty(Request $request): JsonResponse
    {
        if (!$this->checkContentType($request->headers->get('content-type'))) {
            return $this->json(ReplyUtils::failure(['message' => 'Content-type must be application/json!']));
        }
        if (!$user = $this->getUser()) {
            return $this->json(ReplyUtils::failure(['message' => 'No user found!']), 403);
        }
        $cacheKey = md5($user->getUserIdentifier());
        $cacheDropResult = $this->cacheUtil->drop($cacheKey);
        if (!$cacheDropResult) {
            return $this->json(ReplyUtils::failure(['data' => [], 'message' => 'An error has occurred while dropping the cache']));
        }
        return $this->json(ReplyUtils::success(['message' => 'success']));
    }


    /**
     * @Route("", name="fetch", methods={"GET"})
     * @throws InvalidArgumentException
     * @OA\Response (
     *     response="200",
     *     description="Fetch customer basket content",
     *     @OA\JsonContent(
     *           @OA\Property(property="status", type="boolean"),
     *           @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *           @OA\Property(property="message", type="string"),
     *        )
     * ),
     * @OA\Tag(name="Basket")
     * @AnnotationSecurity(name="Authorization")
     */
    public function list(Request $request): JsonResponse
    {
        if (!$this->checkContentType($request->headers->get('content-type'))) {
            return $this->json(ReplyUtils::failure(['message' => 'Content-type must be application/json!']));
        }

        if (!$user = $this->getUser()) {
            return $this->json(ReplyUtils::failure(['message' => 'No user found!']), 403);
        }

        $cacheKey = md5($user->getUserIdentifier());
        $fetchBasketFromCache = $this->cacheUtil->fetch($cacheKey);
        if (!$fetchBasketFromCache || !array_key_exists('items', $fetchBasketFromCache)) {
            return $this->json(ReplyUtils::success(['data' => [], 'basketTotal' => 0, 'message' => 'No items in basket']));
        }
        $items = $fetchBasketFromCache['items'];

        $productIds = [];
        foreach ($items as $item) {
            $productIds[] = $item['productId'];
        }

        //Fetch all products in basket at the same time not to get them from repo one by one. I will check if any changes for unitPrice or stock in product. If so, I will update the basket in cache.
        $productRepository = $this->em->getRepository(Product::class);
        $products = $productRepository->fetchProductsByIds($productIds);

        $noStockProducts = [];

        // I set new array which keys are productId. I fetch all products detail at one time from DB and I will get product info without loop
        $rePreparedProducts = [];
        foreach ($products as $product) {
            $rePreparedProducts[$product['id']] = $product;
        }

        foreach ($items as $key => &$item) {
            $product = $rePreparedProducts[$item['productId']];
            // If stock is 0 than remove from basket
            if ($product['stock'] === 0) {
                unset($items[$key]);
                continue;
            }
            if ($item['quantity'] > $product['stock']) {
                $item['quantity'] = $product['stock'];
                $noStockProducts[] = $product['name'];
            }
            $item['unitPrice'] = $product['price'];
            $item['total'] = $product['price'] * $item['quantity'];
        }

        $basketTotal = 0;
        if ($fetchBasketFromCache) {
            $basketTotal = $this->calculateBasketTotal($items);
        }

        $cacheData = [
            'items' => $items,
            'basketTotal' => $basketTotal,
            'basketDiscountedTotal' => $fetchBasketFromCache['basketDiscountedTotal'],
            'discounts' => $fetchBasketFromCache['discounts']
        ];

        $discountResult = $this->calculateDiscount($rePreparedProducts, $cacheData);
        if ($discountResult) {
            $cacheData = $discountResult;
        }

        // I cache basket items again after changes
        $this->cacheUtil->drop($cacheKey);
        $this->cacheUtil->add($cacheKey, $cacheData);

        $message = 'success';
        if ($noStockProducts) {
            $message = implode(',', $noStockProducts) . ' are less than stock. So we changed the quantity of that product in your basket.';
        }
        return $this->json(ReplyUtils::success(['data' => $cacheData, 'basketTotal' => $basketTotal, 'message' => $message]));
    }

    /**
     * @param array $basketItems
     * @param array $alreadyAddedProducts
     * @param array $rePreparedProducts
     * @return array
     */
    private function setItemsWillBeCached(array $basketItems, array $alreadyAddedProducts, array $rePreparedProducts): array
    {
        $itemsWillBeCached = [];

        foreach ($basketItems as $basketItem) {
            if (in_array($basketItem['product'], $alreadyAddedProducts)) {
                continue;
            }
            $product = $rePreparedProducts[$basketItem['product']];
            if ($product) {
                $sub['productId'] = $product['id'];
                $sub['name'] = $product['name'];
                $sub['quantity'] = $basketItem['quantity'];
                $sub['unitPrice'] = $product['price'];
                $sub['total'] = $basketItem['quantity'] * $product['price'];
                $itemsWillBeCached[] = $sub;
            }
        }

        return $itemsWillBeCached;
    }


    /**
     * @param array $newCachedItems
     * @return string
     */
    private function calculateBasketTotal(array $newCachedItems): string
    {
        $total = 0;
        foreach ($newCachedItems as $item) {
            $total += $item['total'];
        }
        return number_format($total, 2, '.', '');
    }
}
