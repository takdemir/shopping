<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Util\ReplyUtils;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Security as AnnotationSecurity;

/**
 * Class ProductController
 * @package App\Controller
 * @Route("/api/v1/product", name="api_v1_product_", schemes={"https","http"})
 * @Security("is_granted('ROLE_ADMIN')")
 */
class ProductController extends AbstractController
{
    use BaseTrait;

    /**
     * @Route("", name="list", methods={"GET"})
     * @OA\Response (
     *     response="200",
     *     description="Returns the all searched products",
     *     @OA\JsonContent(
     *           @OA\Property(property="status", type="boolean"),
     *           @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *           @OA\Property(property="pagesCount", type="integer"),
     *           @OA\Property(property="totalDataCount", type="integer"),
     *        )
     * ),
     * @OA\Parameter (
     *     name="category",
     *     in="query",
     *     description="Category ID",
     *     @OA\Schema (type="integer"),
     * ),
     * @OA\Parameter (
     *     name="is_category_active",
     *     in="query",
     *     description="Category active status",
     *     @OA\Schema (type="boolean"),
     * ),
     * @OA\Parameter (
     *     name="name",
     *     in="query",
     *     description="Category name",
     *     @OA\Schema (type="string"),
     * ),
     * @OA\Parameter (
     *     name="is_active",
     *     in="query",
     *     description="Product active status",
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
     * @OA\Tag(name="Product")
     * @AnnotationSecurity(name="Authorization")
     */
    public function list(Request $request): JsonResponse
    {
        if (!$this->checkContentType($request->headers->get('content-type'))) {
            return $this->json(ReplyUtils::failure(['message' => 'Content-type must be application/json!']));
        }

        $query = $request->query->all();

        $category = null;
        if (array_key_exists('category', $query) && is_int((int)$query['category'])) {
            $category = (int)$query['category'];
        }

        $name = null;
        if (array_key_exists('name', $query) && !trim($query['name'])) {
            $name = trim($query['name']);
        }

        $isCategoryActive = null;
        if (array_key_exists('is_category_active', $query) && is_bool((bool)$query['is_category_active'])) {
            $isCategoryActive = (bool)$query['is_category_active'];
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
            'category' => $category,
            'name' => $name,
            'isCategoryActive' => $isCategoryActive,
            'isActive' => $isActive,
            'page' => $page,
            'offset' => $offset,
        ];

        $productRepository = $this->em->getRepository(Product::class);
        $products = $productRepository->search($parameters);
        return $this->json(ReplyUtils::success($products));
    }

    /**
     * @Route("/{id<^\d+$>}", name="show", methods="GET")
     * @OA\Response (
     *     response="200",
     *     description="Returns a product info",
     *     @OA\JsonContent(
     *           @OA\Property(property="status", type="boolean"),
     *           @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *           @OA\Property(property="message", type="string"),
     *        )
     * )
     *
     * @OA\Tag(name="Product")
     * @AnnotationSecurity(name="Authorization")
     */
    public function show(Request $request, Product $product): JsonResponse
    {
        if (!$this->checkContentType($request->headers->get('content-type'))) {
            return $this->json(ReplyUtils::failure(['message' => 'Content-type must be application/json!']));
        }

        return $this->json(ReplyUtils::success(['data' => $product, 'message' => 'success']));
    }


    /**
     * @Route("", name="create", methods={"POST"})
     * @OA\Response (
     *     response="200",
     *     description="Creates a new product",
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
     *                          property="category",
     *                          description="Category ID",
     *                          type="integer"
     *                  ),
     *                  @OA\Property (
     *                          property="name",
     *                          description="Product name",
     *                          type="string"
     *                  ),
     *                  @OA\Property (
     *                          property="price",
     *                          description="Product price as float",
     *                          type="float"
     *                  ),
     *                  @OA\Property (
     *                          property="currency",
     *                          description="Product price currency. Must be one TL, USD, EURO",
     *                          type="string"
     *                  ),
     *                  @OA\Property (
     *                          property="stock",
     *                          description="Product stock as integer",
     *                          type="integer"
     *                  ),
     *                  @OA\Property (
     *                          property="description",
     *                          description="Product decsription. Not required",
     *                          type="string"
     *                  ),
     *                  @OA\Property (
     *                          property="isActive",
     *                          description="Product active status",
     *                          type="boolean"
     *                  ),
     *           )
     *      )
     * )
     * @OA\Tag(name="Product")
     * @AnnotationSecurity(name="Authorization")
     */
    public function create(Request $request): JsonResponse
    {
        try {
            if (!$this->checkContentType($request->headers->get('content-type'))) {
                return $this->json(ReplyUtils::failure(['message' => 'Content-type must be application/json!']));
            }

            $postedData = json_decode($request->getContent(), true);

            $product = new Product();
            $form = $this->createForm(ProductType::class, $product);
            $form->submit($postedData);
            $form->handleRequest($request);

            if (!$form->isValid() && $errors = (string)$form->getErrors(true, false)) {
                return $this->json(ReplyUtils::failure(['message' => $errors]));
            }

            $this->em->persist($product);
            $this->em->flush();

            if (!$product->getId()) {
                return $this->json(ReplyUtils::failure(['message' => 'Creation is failed']));
            }

            return $this->json(ReplyUtils::success(['data' => $product->getId(), 'message' => 'success']));

        } catch (\Exception $exception) {
            //TODO: Log
            return $this->json(ReplyUtils::failure(['message' => $exception->getMessage()]), 500);
        }
    }


    /**
     * @Route("/{id<^\d+$>}", name="edit", methods={"PUT"})
     * @OA\Response (
     *     response="200",
     *     description="Edits a product",
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
     *                          property="category",
     *                          description="Category ID",
     *                          type="integer"
     *                  ),
     *                  @OA\Property (
     *                          property="name",
     *                          description="Product name",
     *                          type="string"
     *                  ),
     *                  @OA\Property (
     *                          property="price",
     *                          description="Product price as float",
     *                          type="float"
     *                  ),
     *                  @OA\Property (
     *                          property="currency",
     *                          description="Product price currency. Must be one TL, USD, EURO",
     *                          type="string"
     *                  ),
     *                  @OA\Property (
     *                          property="stock",
     *                          description="Product stock as integer",
     *                          type="integer"
     *                  ),
     *                  @OA\Property (
     *                          property="description",
     *                          description="Product decsription. Not required",
     *                          type="string"
     *                  ),
     *                  @OA\Property (
     *                          property="isActive",
     *                          description="Product active status",
     *                          type="boolean"
     *                  ),
     *           )
     *      )
     * )
     * @OA\Tag(name="Product")
     * @AnnotationSecurity(name="Authorization")
     */
    public function edit(Request $request, Product $product): JsonResponse
    {
        try {
            if (!$this->checkContentType($request->headers->get('content-type'))) {
                return $this->json(ReplyUtils::failure(['message' => 'Content-type must be application/json!']));
            }

            $postedData = json_decode($request->getContent(), true);

            $form = $this->createForm(ProductType::class, $product);
            $form->submit($postedData);
            $form->handleRequest($request);

            if (!$form->isValid() && $errors = (string)$form->getErrors(true, false)) {
                return $this->json(ReplyUtils::failure(['message' => $errors]));
            }

            $this->em->persist($product);
            $this->em->flush();

            return $this->json(ReplyUtils::success(['data' => $product->getId(), 'message' => 'success']));

        } catch (\Exception $exception) {
            //TODO: Log
            return $this->json(ReplyUtils::failure(['message' => $exception->getMessage()]), 500);
        }
    }
}
