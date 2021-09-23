<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Util\ReplyUtils;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Security as AnnotationSecurity;

/**
 * Class CategoryController
 * @package App\Controller
 * @Route("/api/v1/category", name="api_v1_category_", schemes={"https","http"})
 * @Security("is_granted('ROLE_ADMIN')")
 */
class CategoryController extends AbstractController
{
    use BaseTrait;

    /**
     * @Route("", name="category", methods={"GET"})
     * @OA\Response (
     *     response="200",
     *     description="Returns the all searched categories",
     *     @OA\JsonContent(
     *           @OA\Property(property="status", type="boolean"),
     *           @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *           @OA\Property(property="pagesCount", type="integer"),
     *           @OA\Property(property="totalDataCount", type="integer"),
     *        )
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
     *     description="Category active status",
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
     * @OA\Tag(name="Category")
     * @AnnotationSecurity(name="Authorization")
     */
    public function list(Request $request): JsonResponse
    {
        if (!$this->checkContentType($request->headers->get('content-type'))) {
            return $this->json(ReplyUtils::failure(['message' => 'Content-type must be application/json!']));
        }

        $query = $request->query->all();

        $name = null;
        if (array_key_exists('name', $query) && !trim($query['name'])) {
            $name = trim($query['name']);
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
            'name' => $name,
            'isActive' => $isActive,
            'page' => $page,
            'offset' => $offset,
        ];

        $categoryRepository = $this->em->getRepository(Category::class);
        $categories = $categoryRepository->search($parameters);
        return $this->json(ReplyUtils::success($categories));
    }


    /**
     * @Route("/{id<^\d+$>}", name="show", methods="GET")
     * @OA\Response (
     *     response="200",
     *     description="Returns a category info",
     *     @OA\JsonContent(
     *           @OA\Property(property="status", type="boolean"),
     *           @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *           @OA\Property(property="message", type="string"),
     *        )
     * )
     *
     * @OA\Tag(name="Category")
     * @AnnotationSecurity(name="Authorization")
     */
    public function show(Request $request, Category $category): JsonResponse
    {
        if (!$this->checkContentType($request->headers->get('content-type'))) {
            return $this->json(ReplyUtils::failure(['message' => 'Content-type must be application/json!']));
        }

        return $this->json(ReplyUtils::success(['data' => $category, 'message' => 'success']));
    }


    /**
     * @Route("", name="create", methods={"POST"})
     * @OA\Response (
     *     response="200",
     *     description="Creates a new category",
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
     *                          property="name",
     *                          description="Category name",
     *                          type="string"
     *                  ),
     *           )
     *      )
     * )
     * @OA\Tag(name="Category")
     * @AnnotationSecurity(name="Authorization")
     */
    public function create(Request $request): JsonResponse
    {
        try {
            if (!$this->checkContentType($request->headers->get('content-type'))) {
                return $this->json(ReplyUtils::failure(['message' => 'Content-type must be application/json!']));
            }

            $postedData = json_decode($request->getContent(), true);

            $category = new Category();
            $form = $this->createForm(CategoryType::class, $category);
            $form->submit($postedData);
            $form->handleRequest($request);

            if (!$form->isValid() && $errors = (string)$form->getErrors(true, false)) {
                return $this->json(ReplyUtils::failure(['message' => $errors]));
            }
            
            $this->em->persist($category);
            $this->em->flush();

            if (!$category->getId()) {
                return $this->json(ReplyUtils::failure(['message' => 'Creation is failed']));
            }

            return $this->json(ReplyUtils::success(['data' => $category->getId(), 'message' => 'success']));

        } catch (\Exception $exception) {
            //TODO: Log
            return $this->json(ReplyUtils::failure(['message' => $exception->getMessage()]), 500);
        }
    }

    /**
     * @Route("/{id<^\d+$>}", name="edit", methods={"PUT"})
     * @OA\Response (
     *     response="200",
     *     description="Edits a category",
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
     *                          property="name",
     *                          description="Category name",
     *                          type="string"
     *                  ),
     *           )
     *      )
     * )
     * @OA\Tag(name="Category")
     * @AnnotationSecurity(name="Authorization")
     */
    public function edit(Request $request, Category $category): JsonResponse
    {
        try {
            if (!$this->checkContentType($request->headers->get('content-type'))) {
                return $this->json(ReplyUtils::failure(['message' => 'Content-type must be application/json!']));
            }

            $postedData = json_decode($request->getContent(), true);

            $form = $this->createForm(CategoryType::class, $category);
            $form->submit($postedData);
            $form->handleRequest($request);

            if (!$form->isValid() && $errors = (string)$form->getErrors(true, false)) {
                return $this->json(ReplyUtils::failure(['message' => $errors]));
            }

            $this->em->persist($category);
            $this->em->flush();

            return $this->json(ReplyUtils::success(['data' => $category->getId(), 'message' => 'success']));

        } catch (\Exception $exception) {
            //TODO: Log
            return $this->json(ReplyUtils::failure(['message' => $exception->getMessage()]), 500);
        }
    }
}
