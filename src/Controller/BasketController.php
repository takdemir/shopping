<?php

namespace App\Controller;

use App\Entity\Discount;
use App\Entity\Product;
use App\Service\DiscountInterface;
use App\Service\PercentOverDiscount;
use App\Service\BuyNDecreasePercentDiscount;
use App\Service\BuyNPayKDiscount;
use App\Util\ReplyUtils;
use Doctrine\ORM\AbstractQuery;
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

        // Check the payload in posted data
        if (!array_key_exists('items', $postedData) || !$postedData['items']) {
            return $this->json(ReplyUtils::success(['message' => 'No items found in the basket!']));
        }

        //  I sent product IDs extra in payload of posted data to prevent looping in basket items to get product IDs,
        if (!array_key_exists('productIds', $postedData) || !$postedData['productIds']) {
            return $this->json(ReplyUtils::success(['message' => 'No productIds found in the basket!']));
        }

        // Start to basket process
        $processResult = $this->process($postedData);

        if (!$processResult['status']) {
            return $this->json($processResult);
        }
        $cacheData = $processResult['data'];
        $message = $processResult['message'];

        //dd($cacheData);

        $cacheKey = md5($user->getUserIdentifier());

        $cacheDropResult = $this->cacheUtil->drop($cacheKey);
        if (!$cacheDropResult) {
            return $this->json(ReplyUtils::failure(['data' => [], 'message' => 'An error has occurred while dropping the cache']));
        }
        $cacheAddResult = $this->cacheUtil->add($cacheKey, $cacheData);
        if (!$cacheAddResult) {
            return $this->json(ReplyUtils::failure(['data' => [], 'message' => 'An error has occurred while adding to basket cache']));
        }
        return $this->json(ReplyUtils::success(['data' => $cacheData, 'message' => $message]));
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

        $outOfStockProducts = [];

        foreach ($items as $key => &$item) {
            $product = $products[$item['productId']];
            // If stock is 0 than remove from basket
            if ($product['stock'] === 0) {
                unset($items[$key]);
                continue;
            }
            if ($item['quantity'] > $product['stock']) {
                $item['quantity'] = $product['stock'];
                $outOfStockProducts[] = $product['name'];
            }
            $item['unitPrice'] = $product['price'];
            $item['total'] = number_format($product['price'] * $item['quantity'], 2, ',', '');
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

        $discountResult = $this->calculateDiscount($products, $cacheData);
        if ($discountResult) {
            $cacheData = $discountResult;
        }

        // I cache basket items again after changes
        $this->cacheUtil->drop($cacheKey);
        $this->cacheUtil->add($cacheKey, $cacheData);

        $message = 'success';
        if ($outOfStockProducts) {
            $message = implode(',', $outOfStockProducts) . ' are less than stock. So we changed the quantity of that product in your basket.';
        }
        return $this->json(ReplyUtils::success(['data' => $cacheData, 'message' => $message]));
    }

    /**
     * @param array $postedItems
     * @param array $alreadyAddedProducts
     * @param array $products
     * @return array
     */
    private function setPostedItemsWillBeReCached(array $postedItems, array $alreadyAddedProducts, array $products): array
    {
        $itemsWillBeReCached = [];

        foreach ($postedItems as $postedItem) {
            if (in_array($postedItem['product'], $alreadyAddedProducts)) {
                continue;
            }
            $product = $products[$postedItem['product']];
            $category = $product['category'];
            if ($product) {
                $sub['categoryId'] = $category['id'];
                $sub['productId'] = $product['id'];
                $sub['name'] = $product['name'];
                $sub['quantity'] = $postedItem['quantity'];
                $sub['unitPrice'] = $product['price'];
                $sub['total'] = number_format($postedItem['quantity'] * $product['price'], 2, ',', '');
                $itemsWillBeReCached[] = $sub;
            }
        }

        return $itemsWillBeReCached;
    }


    /**
     * @param array $mergedBasketItems
     * @return string
     */
    private function calculateBasketTotal(array $mergedBasketItems): string
    {
        $total = 0;
        foreach ($mergedBasketItems as $item) {
            $total += (float)$item['total'];
        }
        return number_format($total, 2, '.', '');
    }

    /**
     * @param array $products
     * @param array $cacheData
     * @return array
     */
    private function calculateDiscount(array $products, array $cacheData): array
    {
        // First, let's fetch all active, started and not expired discounts.
        $now = new \DateTime();
        $discountRepository = $this->em->getRepository(Discount::class);
        $availableDiscounts = $discountRepository->fetchAvailableDiscounts(AbstractQuery::HYDRATE_OBJECT);

        // If no available discount, return cache data own.
        if (!$availableDiscounts) {
            return $cacheData;
        }
        $discountedCacheData = [];
        //dd($availableDiscounts);

        foreach ($availableDiscounts as $discount) {

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

            $discountedCacheData = $discountCalculator->calculateDiscount($cacheData, $discount);
        }

        return $discountedCacheData;
    }

    /**
     * @param array $postedData
     * @return array
     * @throws InvalidArgumentException
     */
    private function process(array $postedData): array
    {
        if (!$user = $this->getUser()) {
            return ReplyUtils::failure(['message' => 'No user found!']);
        }

        // Creating a unique cache key for customer
        $cacheKey = md5($user->getUserIdentifier());
        $postedItems = $postedData['items'];

        //Fetch all products details in basket at the same time not to get them from repo one by one
        $productRepository = $this->em->getRepository(Product::class);
        $products = $productRepository->fetchProductsByIds($postedData['productIds']);

        // I will collect any invalid items or out of stock product in basket items into below variables while checking the data
        $anyInValidDataInPostedItems = [];
        $outOfStockProducts = [];

        // Started to check postedItems if any missing parameter or any out of stock product
        foreach ($postedItems as $postedItem) {
            if (!array_key_exists('product', $postedItem) || !$postedItem['product']) {
                $anyInValidDataInPostedItems[] = $postedItem;
                break;
            }
            if (!array_key_exists('quantity', $postedItem) || $postedItem['quantity'] === 0 || !is_int((int)$postedItem['quantity'])) {
                $anyInValidDataInPostedItems[] = $postedItem;
                break;
            }
            $product = $products[$postedItem['product']];
            if ($postedItem['quantity'] > $product['stock']) {
                $outOfStockProducts[] = $product['name'];
            }
        }

        if ($anyInValidDataInPostedItems) {
            return ReplyUtils::failure(['message' => 'All items objects must contain product and quantity info. Please check it!']);
        }

        if ($outOfStockProducts) {
            return ReplyUtils::failure(['message' => 'No enough stock for ' . implode(',', $outOfStockProducts)]);
        }


        // After checking payload in posted data, Let's check if customer has a data in cache.
        $fetchBasketFromCache = $this->cacheUtil->fetch($cacheKey);

        // I will merge previous cached basket and new posted items in $itemsWillBeReCached variable below
        $itemsWillBeReCached = [];

        // There may be a customer cached basket. That is why, I check the data in cache.
        // If so, I will merge the new basket data to cached file, else I will add it to basket as a new item

        $alreadyAddedProducts = [];

        if ($fetchBasketFromCache && array_key_exists('items', $fetchBasketFromCache)) {
            // Get basket items in the cache
            $cachedItems = $fetchBasketFromCache['items'];

            // Compare the posted items and cached items to find the same products and increase the quantity and total of that product
            foreach ($postedItems as $postedItem) {
                $product = $products[$postedItem['product']];
                foreach ($cachedItems as $key => $cachedItem) {
                    if ($cachedItem['productId'] === $postedItem['product']) {

                        // If product stock is exhausted, then remove that item
                        if ($product['stock'] === 0) {
                            unset($cachedItems[$key]);
                            continue;
                        }

                        // Increase the quantity
                        $newQuantity = $cachedItem['quantity'] + $postedItem['quantity'];

                        // Let's check the new quantity again for stock. If stock is not enough, I will warn the customer.
                        if ($newQuantity > $product['stock']) {
                            $outOfStockProducts[] = $product['name'];

                            // If products stock is less than new added quantity, check cached item quantity.
                            // If it is also greater than product stock than new quantity will be available product stock
                            $newQuantity = ($cachedItem['quantity'] < $product['stock']) ? $cachedItem['quantity'] : $product['stock'];
                        }
                        $alreadyAddedProducts[] = $postedItem['product'];
                        $cachedItem['quantity'] = $newQuantity;
                        // Unit price may be changed. So I get the unitPrice again from $product.
                        $cachedItem['total'] = number_format($cachedItem['quantity'] * $product['price'], 2, '.', '');
                        $itemsWillBeReCached[] = $cachedItem;
                    }
                }
            }
        }

        $mergedBasketItems = array_merge($itemsWillBeReCached, $this->setPostedItemsWillBeReCached($postedItems, $alreadyAddedProducts, $products));

        $message = "success";
        if ($outOfStockProducts) {
            $message = 'No more stock for ' . implode(',', $outOfStockProducts) . '. So I couldn\'t increase their quantities.';
        }

        $basketTotal = 0;
        if ($mergedBasketItems) {
            $basketTotal = $this->calculateBasketTotal($mergedBasketItems);
        }

        $cacheData = [
            'items' => $mergedBasketItems,
            'basketTotal' => $basketTotal,
            'basketDiscountedTotal' => $basketTotal,
            'discounts' => []
        ];

        // Let's start to calculate discounts
        $discountResult = $this->calculateDiscount($products, $cacheData);

        return ReplyUtils::success(['data' => $discountResult, 'message' => $message]);
    }
}
