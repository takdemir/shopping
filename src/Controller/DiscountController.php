<?php

namespace App\Controller;

use App\Entity\Discount;
use App\Form\DiscountType;
use App\Util\ReplyUtils;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Security as AnnotationSecurity;

/**
 * Class DiscountController
 * @package App\Controller
 * @Route("/api/v1/discount", name="api_v1_discount_", schemes={"https","http"})
 * @Security("is_granted('ROLE_ADMIN')")
 */
class DiscountController extends BaseController
{
    use BaseTrait;

    /**
     * @Route("", name="list", methods={"GET"})
     * @OA\Response (
     *     response="200",
     *     description="Returns the all searched discounts",
     *     @OA\JsonContent(
     *           @OA\Property(property="status", type="boolean"),
     *           @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *           @OA\Property(property="pagesCount", type="integer"),
     *           @OA\Property(property="totalDataCount", type="integer"),
     *        )
     * ),
     * @OA\Parameter (
     *     name="user",
     *     in="query",
     *     description="User ID",
     *     @OA\Schema (type="integer"),
     * ),
     * @OA\Parameter (
     *     name="category",
     *     in="query",
     *     description="Category ID",
     *     @OA\Schema (type="integer"),
     * ),
     * @OA\Parameter (
     *     name="product",
     *     in="query",
     *     description="Product ID",
     *     @OA\Schema (type="integer"),
     * ),
     * @OA\Parameter (
     *     name="discount_code_name",
     *     in="query",
     *     description="Discount code name",
     *     @OA\Schema (type="string"),
     * ),
     * @OA\Parameter (
     *     name="is_active",
     *     in="query",
     *     description="Discount active status",
     *     @OA\Schema (type="boolean"),
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
     * @OA\Tag(name="Discount")
     * @AnnotationSecurity(name="Authorization")
     */
    public function list(Request $request): JsonResponse
    {
        if (!$this->checkContentType($request->headers->get('content-type'))) {
            return $this->json(ReplyUtils::failure(['message' => 'Content-type must be application/json!']));
        }

        $query = $request->query->all();

        $user = null;
        if (array_key_exists('user', $query) && is_int((int)$query['user'])) {
            $user = (int)$query['user'];
        }

        $category = null;
        if (array_key_exists('category', $query) && is_int((int)$query['category'])) {
            $category = (int)$query['category'];
        }

        $product = null;
        if (array_key_exists('product', $query) && is_int((int)$query['product'])) {
            $product = (int)$query['product'];
        }

        $discountCodeName = null;
        if (array_key_exists('discount_code_name', $query) && !trim($query['discount_code_name'])) {
            $discountCodeName = trim($query['discount_code_name']);
        }

        $isActive = null;
        if (array_key_exists('is_active', $query) && is_bool((bool)$query['is_active'])) {
            $isActive = (bool)$query['is_active'];
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
            'category' => $category,
            'product' => $product,
            'discountCodeName' => $discountCodeName,
            'isActive' => $isActive,
            'page' => $page,
            'offset' => $offset,
        ];

        $discountRepository = $this->em->getRepository(Discount::class);
        $discounts = $discountRepository->search($parameters);
        return $this->json(ReplyUtils::success($discounts));
    }


    /**
     * @Route("/{id<^\d+$>}", name="show", methods="GET")
     * @OA\Response (
     *     response="200",
     *     description="Returns a discount info",
     *     @OA\JsonContent(
     *           @OA\Property(property="status", type="boolean"),
     *           @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *           @OA\Property(property="message", type="string"),
     *        )
     * )
     *
     * @OA\Tag(name="Discount")
     * @AnnotationSecurity(name="Authorization")
     */
    public function show(Request $request, Discount $discount): JsonResponse
    {
        if (!$this->checkContentType($request->headers->get('content-type'))) {
            return $this->json(ReplyUtils::failure(['message' => 'Content-type must be application/json!']));
        }

        return $this->json(ReplyUtils::success(['data' => $discount, 'message' => 'success']));
    }


    /**
     * @Route("", name="create", methods={"POST"})
     * @OA\Response (
     *     response="200",
     *     description="Creates a new discount",
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
     *                  ),
     *                  @OA\Property (
     *                          property="category",
     *                          description="Category ID",
     *                          type="integer"
     *                  ),
     *                  @OA\Property (
     *                          property="product",
     *                          description="Product ID",
     *                          type="integer"
     *                  ),
     *                  @OA\Property (
     *                          property="discountCode",
     *                          description="Discount Code Name",
     *                          type="string"
     *                  ),
     *                  @OA\Property (
     *                          property="discountClassName",
     *                          description="Discount Class Name in the project",
     *                          type="string"
     *                  ),
     *                  @OA\Property (
     *                          property="isActive",
     *                          description="Active status",
     *                          type="boolean"
     *                  ),
     *                  @OA\Property (
     *                          property="startAt",
     *                          description="Discount start date",
     *                          type="datetime"
     *                  ),
     *                  @OA\Property (
     *                          property="expireAt",
     *                          description="Discount expire date",
     *                          type="datetime"
     *                  ),
     *           )
     *      )
     * )
     * @OA\Tag(name="Discount")
     * @AnnotationSecurity(name="Authorization")
     */
    public function create(Request $request): JsonResponse
    {
        try {
            if (!$this->checkContentType($request->headers->get('content-type'))) {
                return $this->json(ReplyUtils::failure(['message' => 'Content-type must be application/json!']));
            }

            $postedData = json_decode($request->getContent(), true);

            $discount = new Discount();
            $form = $this->createForm(DiscountType::class, $discount);

            $form->submit($postedData);
            $form->handleRequest($request);

            if (!$form->isValid() && $errors = (string)$form->getErrors(true, false)) {
                return $this->json(ReplyUtils::failure(['message' => $errors]));
            }

            $this->em->persist($discount);
            $this->em->flush();

            if (!$discount->getId()) {
                return $this->json(ReplyUtils::failure(['message' => 'Creation is failed']));
            }

            return $this->json(ReplyUtils::success(['data' => $discount->getId(), 'message' => 'success']));

        } catch (\Exception $exception) {
            //TODO: Log
            return $this->json(ReplyUtils::failure(['message' => $exception->getMessage()]), 500);
        }
    }


    /**
     * @Route("/{id<^\d+$>}", name="edit", methods={"PUT"})
     * @OA\Response (
     *     response="200",
     *     description="Edits a discount",
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
     *                  ),
     *                  @OA\Property (
     *                          property="category",
     *                          description="Category ID",
     *                          type="integer"
     *                  ),
     *                  @OA\Property (
     *                          property="product",
     *                          description="Product ID",
     *                          type="integer"
     *                  ),
     *                  @OA\Property (
     *                          property="discountCode",
     *                          description="Discount Code Name",
     *                          type="string"
     *                  ),
     *                  @OA\Property (
     *                          property="discountClassName",
     *                          description="Discount Class Name in the project",
     *                          type="string"
     *                  ),
     *                  @OA\Property (
     *                          property="isActive",
     *                          description="Active status",
     *                          type="boolean"
     *                  ),
     *                  @OA\Property (
     *                          property="startAt",
     *                          description="Discount start date",
     *                          type="datetime"
     *                  ),
     *                  @OA\Property (
     *                          property="expireAt",
     *                          description="Discount expire date",
     *                          type="datetime"
     *                  ),
     *           )
     *      )
     * )
     * @OA\Tag(name="Discount")
     * @AnnotationSecurity(name="Authorization")
     */
    public function edit(Request $request, Discount $discount): JsonResponse
    {
        try {
            if (!$this->checkContentType($request->headers->get('content-type'))) {
                return $this->json(ReplyUtils::failure(['message' => 'Content-type must be application/json!']));
            }

            $postedData = json_decode($request->getContent(), true);

            $form = $this->createForm(DiscountType::class, $discount);

            $form->submit($postedData);
            $form->handleRequest($request);

            if (!$form->isValid() && $errors = (string)$form->getErrors(true, false)) {
                return $this->json(ReplyUtils::failure(['message' => $errors]));
            }

            $this->em->persist($discount);
            $this->em->flush();

            return $this->json(ReplyUtils::success(['data' => $discount->getId(), 'message' => 'success']));

        } catch (\Exception $exception) {
            //TODO: Log
            return $this->json(ReplyUtils::failure(['message' => $exception->getMessage()]), 500);
        }
    }

}
