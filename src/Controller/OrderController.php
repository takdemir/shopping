<?php

namespace App\Controller;

use App\Entity\Order;
use App\Form\OrderType;
use App\Util\ReplyUtils;
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
        $order->setIsActive(false);
        $this->em->persist($order);
        $this->em->flush($order);

        return $this->json(ReplyUtils::success(['data' => $order, 'message' => 'success']));
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

            $this->em->persist($order);
            $this->em->flush();

            if (!$order->getId()) {
                return $this->json(ReplyUtils::failure(['message' => 'Creation is failed']));
            }

            return $this->json(ReplyUtils::success(['data' => $order->getId(), 'message' => 'success']));

        } catch (\Exception $exception) {
            //TODO: Log
            return $this->json(ReplyUtils::failure(['message' => $exception->getMessage()]), 500);
        }
    }
}
