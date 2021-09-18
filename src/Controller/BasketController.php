<?php

namespace App\Controller;

use App\Entity\Product;
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

        // Not to loop in items to get product IDs, I extra sent product IDs
        if (!array_key_exists('productIds', $postedData) || !$postedData['productIds']) {
            return $this->json(ReplyUtils::success(['message' => 'No productIds found in the basket!']));
        }


        $cacheKey = md5($user->getUserIdentifier());
        $basketItems = $postedData['items'];

        $productRepository = $this->em->getRepository(Product::class);
        $products = $productRepository->fetchProductsByIds($postedData['productIds']);

        $noValidDataInBasketItems = [];
        $noStockProducts = [];

        foreach ($basketItems as $basketItem) {
            if (!array_key_exists('product', $basketItem) || !$basketItem['product']) {
                $noValidDataInBasketItems[] = $basketItem;
                break;
            }
            if (!array_key_exists('quantity', $basketItem) || $basketItem['quantity'] === 0 || !is_int((int)$basketItem['quantity'])) {
                $noValidDataInBasketItems[] = $basketItem;
                break;
            }
            foreach ($products as $product) {
                if ($product['id'] === $basketItem['product'] && $basketItem['quantity'] > $product['stock']) {
                    $noStockProducts[] = $product['name'];
                }
            }
        }

        if ($noValidDataInBasketItems) {
            return $this->json(ReplyUtils::failure(['message' => 'All items objects must contain product and quantity info. Please check it!']));
        }

        if ($noStockProducts) {
            return $this->json(ReplyUtils::failure(['message' => 'No enough stock for ' . implode(',', $noStockProducts)]));
        }

        // I set new array which keys are productId. I will get product info without loop
        $rePreparedProducts = [];
        foreach ($products as $product) {
            $rePreparedProducts[$product['id']] = $product;
        }

        $fetchBasketFromCache = $this->cacheUtil->fetch($cacheKey);

        $itemsWillBeCached = [];

        // If there is a customer cached basket, I will check the cache data and I will merge the new basket data to cached file, else i will add it to basket as new item

        $alreadyAddedProducts = [];

        if ($fetchBasketFromCache) {
            //dd($fetchBasketFromCache);
            foreach ($basketItems as $basketItem) {
                $product = $rePreparedProducts[$basketItem['product']];
                foreach ($fetchBasketFromCache as $cachedItem) {
                    if ($cachedItem['productId'] === $basketItem['product']) {
                        $newQuantity = $cachedItem['quantity'] + $basketItem['quantity'];
                        // Let's check the new quantity again for stock. If no enough stock, I will warn the customer.
                        if ($newQuantity > $product['stock']) {
                            $noStockProducts[] = $product['name'];
                            $newQuantity = $cachedItem['quantity'];
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
        $basketTotal = $this->calculateBasketTotal($newCachedItems);
        $message = "success";
        if ($noStockProducts) {
            $message = 'No more stock for ' . implode(',', $noStockProducts) . '. So I couldn\'t increase their quantities.';
        }

        $cacheDropResult = $this->cacheUtil->drop($cacheKey);
        if (!$cacheDropResult) {
            return $this->json(ReplyUtils::failure(['data' => [], 'message' => 'An error has occurred while dropping the cache']));
        }
        $cacheAddResult = $this->cacheUtil->add($cacheKey, $newCachedItems);
        if (!$cacheAddResult) {
            return $this->json(ReplyUtils::failure(['data' => [], 'message' => 'An error has occurred while adding to basket cache']));
        }
        return $this->json(ReplyUtils::success(['data' => $newCachedItems, 'basketTotal' => $basketTotal, 'message' => $message]));
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

        $newBasketItems = array_filter($fetchBasketFromCache, static function ($item) use ($query) {
            return $item['productId'] !== (int)$query['product'];
        });

        $cacheDropResult = $this->cacheUtil->drop($cacheKey);
        if (!$cacheDropResult) {
            return $this->json(ReplyUtils::failure(['data' => [], 'message' => 'An error has occurred while dropping the cache']));
        }
        $cacheAddResult = $this->cacheUtil->add($cacheKey, $newBasketItems);
        if (!$cacheAddResult) {
            return $this->json(ReplyUtils::failure(['data' => [], 'message' => 'An error has occurred while adding to basket cache']));
        }
        $basketTotal = $this->calculateBasketTotal($newBasketItems);
        return $this->json(ReplyUtils::success(['data' => $newBasketItems, 'basketTotal' => $basketTotal, 'message' => 'success']));
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
        $basketTotal = $this->calculateBasketTotal($fetchBasketFromCache);
        return $this->json(ReplyUtils::success(['data' => $fetchBasketFromCache, 'basketTotal' => $basketTotal, 'message' => 'success']));
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
