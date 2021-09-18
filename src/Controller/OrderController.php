<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Product;
use App\Entity\User;
use App\Form\OrderType;
use App\Util\ReplyUtils;
use Doctrine\ORM\AbstractQuery;
use Psr\Cache\InvalidArgumentException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Security as AnnotationSecurity;

/**
 * Class OrderController
 * @package App\Controller
 * @Route("/api/v1/order", name="api_v1_order_", schemes={"https","http"})
 * @Security("is_granted('ROLE_USER')")
 */
class OrderController extends BaseController
{
    use BaseTrait;

    /**
     * @Route("", name="list", methods={"GET"})
     * @OA\Response (
     *     response="200",
     *     description="Returns the all searched orders",
     *     @OA\JsonContent(
     *           @OA\Property(property="status", type="boolean"),
     *           @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *           @OA\Property(property="pagesCount", type="integer"),
     *           @OA\Property(property="totalDataCount", type="integer"),
     *        )
     * ),
     * @OA\Parameter (
     *     name="customer",
     *     in="query",
     *     description="Customer ID",
     *     @OA\Schema (type="integer"),
     * ),
     * @OA\Parameter (
     *     name="page",
     *     in="query",
     *     description="Page",
     *     @OA\Schema (type="integer"),
     * ),
     * @OA\Parameter (
     *     name="offset",
     *     in="query",
     *     description="Offset",
     *     @OA\Schema (type="integer"),
     * )
     * @OA\Tag(name="Order")
     * @AnnotationSecurity(name="Authorization")
     */
    public function list(Request $request): JsonResponse
    {
        if (!$this->checkContentType($request->headers->get('content-type'))) {
            return $this->json(ReplyUtils::failure(['message' => 'Content-type must be application/json!']));
        }

        $query = $request->query->all();

        $user = null;
        if (array_key_exists('customer', $query) && is_int((int)$query['customer'])) {
            $user = (int)$query['customer'];
        }

        if (($checkAuthorisation = $this->checkUserAuthorisation($this->getUser()->getId())) && !$checkAuthorisation['status']) {
            return $this->json($checkAuthorisation, 403);
        }

        if (!$this->isGranted('ROLE_ADMIN')) {
            $user = $this->getUser()->getId();
        }

        $page = 1;
        if (array_key_exists('page', $query) && is_int((int)$query['page'])) {
            $page = (int)$query['page'];
        }

        $offset = 100;
        if (array_key_exists('offset', $query) && is_int((int)$query['offset'])) {
            $offset = (int)$query['offset'] <= 100 ? (int)$query['offset'] : 100;
        }

        $parameters = [
            'user' => $user,
            'page' => $page,
            'offset' => $offset,
        ];

        $orderRepository = $this->em->getRepository(Order::class);
        $orders = $orderRepository->search($parameters);
        return $this->json(ReplyUtils::success($orders));
    }


    /**
     * @Route("/{id<^\d+$>}", name="show", methods={"GET"})
     * @OA\Response (
     *     response="200",
     *     description="Returns a order info",
     *     @OA\JsonContent(
     *           @OA\Property(property="status", type="boolean"),
     *           @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *           @OA\Property(property="message", type="string"),
     *        )
     * )
     *
     * @OA\Tag(name="Order")
     * @AnnotationSecurity(name="Authorization")
     */
    public function show(Request $request, Order $order): JsonResponse
    {
        if (!$this->checkContentType($request->headers->get('content-type'))) {
            return $this->json(ReplyUtils::failure(['message' => 'Content-type must be application/json!']));
        }

        if (($checkAuthorisation = $this->checkUserAuthorisation($order->getUser()->getId())) && !$checkAuthorisation['status']) {
            return $this->json($checkAuthorisation, 403);
        }

        return $this->json(ReplyUtils::success(['data' => $order, 'message' => 'success']));
    }


    /**
     * @Route("/{id<^\d+$>}", name="delete", methods={"DELETE"})
     * @OA\Response (
     *     response="200",
     *     description="Delete an order",
     *     @OA\JsonContent(
     *           @OA\Property(property="status", type="boolean"),
     *           @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *           @OA\Property(property="message", type="string"),
     *        )
     * )
     *
     * @OA\Tag(name="Order")
     * @AnnotationSecurity(name="Authorization")
     */
    public function delete(Request $request, Order $order): JsonResponse
    {
        if (!$this->checkContentType($request->headers->get('content-type'))) {
            return $this->json(ReplyUtils::failure(['message' => 'Content-type must be application/json!']));
        }
        if (($checkAuthorisation = $this->checkUserAuthorisation($order->getUser()->getId())) && !$checkAuthorisation['status']) {
            return $this->json($checkAuthorisation, 403);
        }
        if ($order->getIsActive() === false) {
            return $this->json(ReplyUtils::success(['message' => 'success']));
        }
        $orderItems = $order->getOrderItems();
        foreach ($orderItems as $orderItem) {
            // After deleting order, I increase the product's stock again. I don't check if the products are refunded or retransferred in that demo.
            $product = $orderItem->getProduct();
            $newStock = $product->getStock() + $orderItem->getQuantity();
            $product->setStock($newStock);
            $orderItem->setIsActive(false);
            $this->em->persist($product);
            $this->em->persist($orderItem);
        }
        $order->setIsActive(false);
        $this->em->persist($order);
        $this->em->flush();

        return $this->json(ReplyUtils::success(['message' => 'success']));
    }


    /**
     * @Route("", name="create", methods={"POST"})
     * @OA\Response (
     *     response="200",
     *     description="Creates a new order",
     *     @OA\JsonContent(
     *           @OA\Property(property="status", type="boolean"),
     *           @OA\Property(property="data", type="object"),
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
     *                          property="user",
     *                          description="User ID",
     *                          type="integer"
     *                  )
     *           )
     *      )
     * )
     * @OA\Tag(name="Order")
     * @AnnotationSecurity(name="Authorization")
     */
    public function create(Request $request): JsonResponse
    {
        try {
            if (!$this->checkContentType($request->headers->get('content-type'))) {
                return $this->json(ReplyUtils::failure(['message' => 'Content-type must be application/json!']));
            }

            $postedData = json_decode($request->getContent(), true);

            if (!$user = $this->getUser()) {
                return $this->json(ReplyUtils::failure(['message' => 'No user found!']), 403);
            }

            $order = new Order();
            $form = $this->createForm(OrderType::class, $order);
            $form->submit($postedData);
            $form->handleRequest($request);

            if (!$form->isValid() && $errors = (string)$form->getErrors(true, false)) {
                return $this->json(ReplyUtils::failure(['message' => $errors]));
            }

            $cacheKey = md5($user->getUserIdentifier());
            $fetchBasketItems = $this->cacheUtil->fetch($cacheKey);
            if (!$fetchBasketItems) {
                return $this->json(ReplyUtils::failure(['message' => 'No items in the basket!']));
            }
            $userRepository = $this->em->getRepository(User::class);
            $productRepository = $this->em->getRepository(Product::class);

            // Let's check the basket stock again. Because stock of products may decrease after cached the items
            $productIds = [];
            foreach ($fetchBasketItems as $item) {
                $productIds[] = $item['productId'];
            }
            //Fetch all products in basket at the same time not to get them from repo one by one
            $products = $productRepository->fetchProductsByIds($productIds, AbstractQuery::HYDRATE_OBJECT);
            if (!$products) {
                return $this->json(ReplyUtils::failure(['message' => 'Products in basket are not valid. Order creation failed!']));
            }

            // I set new array which keys are productId. I fetch all products detail at one time from DB and I will get product info without loop
            $rePreparedProducts = [];
            foreach ($products as $product) {
                $rePreparedProducts[$product->getId()] = $product;
            }

            $total = 0;
            foreach ($fetchBasketItems as $item) {
                $product = $rePreparedProducts[$item['productId']];
                if ($item['quantity'] > $product->getStock()) {
                    return $this->json(ReplyUtils::failure(['message' => 'No enough stock for ' . implode(',', $product['name'])]));
                }
                $orderItem = new OrderItem();
                $orderItem->setIsActive(true);
                $orderItem->setOrderId($order);
                $orderItem->setProduct($rePreparedProducts[$item['productId']]);
                $orderItem->setQuantity($item['quantity']);
                $orderItem->setUnitPrice($item['unitPrice']);
                $orderItem->setTotal($item['total']);
                $total += $item['total'];
                $order->addOrderItem($orderItem);

                // After adding to the order Item, I decrease the stock of the related product
                $leftStock = $product->getStock() - $item['quantity'];
                $product->setStock($leftStock);
                $this->em->persist($product);
            }
            $order->setTotal($total);
            $order->setUser($userRepository->find($user->getId()));
            $order->setIsActive(true);
            $this->em->persist($order);
            $this->em->flush();

            if (!$order->getId()) {
                return $this->json(ReplyUtils::failure(['message' => 'Creation is failed']));
            }

            $this->cacheUtil->drop($cacheKey);

            return $this->json(ReplyUtils::success(['data' => $order->getId(), 'message' => 'success']));

        } catch (InvalidArgumentException | \Exception $exception) {
            //TODO: Log
            return $this->json(ReplyUtils::failure(['message' => $exception->getMessage()]), 500);
        }
    }
}
