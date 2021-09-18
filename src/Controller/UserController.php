<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Util\ReplyUtils;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Security as AnnotationSecurity;

/**
 * Class UserController
 * @package App\Controller
 * @Route("/api/v1/user", name="api_v1_user_", schemes={"https","http"})
 * @Security("is_granted('ROLE_USER')")
 */
class UserController extends BaseController
{
    use BaseTrait;

    /**
     * @Route("", name="list", methods={"GET"})
     * @OA\Response (
     *     response="200",
     *     description="Returns the all searched user accounts",
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
     *     description="User name",
     *     @OA\Schema (type="string"),
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
     * @OA\Tag(name="User")
     * @AnnotationSecurity(name="Authorization")
     */
    public function list(Request $request): JsonResponse
    {
        if (!$this->checkContentType($request->headers->get('content-type'))) {
            return $this->json(ReplyUtils::failure(['message' => 'Content-type must be application/json!']));
        }

        $query = $request->query->all();

        if (($checkAuthorisation = $this->checkUserAuthorisation()) && !$checkAuthorisation['status']) {
            return $this->json($checkAuthorisation, 403);
        }

        $user = null;
        if (!$this->isGranted('ROLE_ADMIN')) {
            $user = $this->getUser()->getId();
        }

        $name = null;
        if (array_key_exists('name', $query) && !trim($query['name'])) {
            $name = trim($query['name']);
        }

        $email = null;
        if (array_key_exists('email', $query) && filter_var(trim($query['email']), FILTER_VALIDATE_EMAIL)) {
            $email = trim($query['email']);
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
            'name' => $name,
            'email' => $email,
            'isActive' => $isActive,
            'page' => $page,
            'offset' => $offset,
        ];

        $userRepository = $this->em->getRepository(User::class);
        $users = $userRepository->search($parameters);
        return $this->json(ReplyUtils::success($users));
    }

    /**
     * @Route("/{id<^\d+$>}", name="show", methods="GET")
     * @OA\Response (
     *     response="200",
     *     description="Returns a user info",
     *     @OA\JsonContent(
     *           @OA\Property(property="status", type="boolean"),
     *           @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *           @OA\Property(property="message", type="string"),
     *        )
     * )
     *
     * @OA\Tag(name="User")
     * @AnnotationSecurity(name="Authorization")
     */
    public function show(Request $request, User $user): JsonResponse
    {
        if (!$this->checkContentType($request->headers->get('content-type'))) {
            return $this->json(ReplyUtils::failure(['message' => 'Content-type must be application/json!']));
        }

        if (($checkAuthorisation = $this->checkUserAuthorisation($this->getUser()->getId())) && !$checkAuthorisation['status']) {
            return $this->json($checkAuthorisation, 403);
        }

        return $this->json(ReplyUtils::success(['data' => $user, 'message' => 'success']));
    }


    /**
     * @Route("", name="create", methods={"POST"})
     * @OA\Response (
     *     response="200",
     *     description="Creates a new user",
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
     *                          property="email",
     *                          description="User email",
     *                          type="string"
     *                  ),
     *                  @OA\Property (
     *                          property="password",
     *                          description="Must be min 8 characters and contain one uppercase and one lowercase letter and one digit and one character at least",
     *                          type="string"
     *                  ),
     *                  @OA\Property (
     *                          property="name",
     *                          description="Name and lastname",
     *                          type="string"
     *                  ),
     *                  @OA\Property (
     *                          property="roles",
     *                          description="Must be in array and can contain only ROLE_USER, ROLE_CUSTOMER, ROLE_ADMIN and ROLE_SUPER_ADMIN",
     *                          type="array",
     *                          @OA\Items(type="string")
     *                  ),
     *           )
     *      )
     * )
     * @OA\Tag(name="User")
     * @AnnotationSecurity(name="Authorization")
     */
    public function create(Request $request, UserPasswordHasherInterface $userPasswordHasher): JsonResponse
    {
        try {
            if (!$this->checkContentType($request->headers->get('content-type'))) {
                return $this->json(ReplyUtils::failure(['message' => 'Content-type must be application/json!']));
            }

            $postedData = json_decode($request->getContent(), true);

            $isUserExistWithThatEmail = $this->userRepository->findOneBy(['email' => $postedData['email']]);
            if ($isUserExistWithThatEmail) {
                return $this->json(ReplyUtils::failure(['message' => 'This email is already exist!']));
            }

            $user = new User();
            $form = $this->createForm(UserType::class, $user);
            $form->submit($postedData);
            $form->handleRequest($request);

            if (!$form->isValid() && $errors = (string)$form->getErrors(true, false)) {
                return $this->json(ReplyUtils::failure(['message' => $errors]));
            }

            $user->setPassword($userPasswordHasher->hashPassword($user, $postedData['password']));
            $user->setRoles($postedData['roles']);
            $this->em->persist($user);
            $this->em->flush();

            if (!$user->getId()) {
                return $this->json(ReplyUtils::failure(['message' => 'Creation is failed']));
            }

            return $this->json(ReplyUtils::success(['data' => $user->getId(), 'message' => 'success']));

        } catch (\Exception $exception) {
            //TODO: Log
            return $this->json(ReplyUtils::failure(['message' => $exception->getMessage()]), 500);
        }
    }


    /**
     * @Route("/{id<^\d+$>}", name="edit", methods={"PUT"})
     * @OA\Response (
     *     response="200",
     *     description="Edits a user",
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
     *                          property="email",
     *                          description="User email",
     *                          type="string"
     *                  ),
     *                  @OA\Property (
     *                          property="password",
     *                          description="Must be min 8 characters and contain one uppercase and one lowercase letter and one digit and one character at least",
     *                          type="string"
     *                  ),
     *                  @OA\Property (
     *                          property="name",
     *                          description="Name and lastname",
     *                          type="string"
     *                  ),
     *                  @OA\Property (
     *                          property="roles",
     *                          description="Must be in array and can contain only ROLE_USER, ROLE_CUSTOMER, ROLE_ADMIN and ROLE_SUPER_ADMIN",
     *                          type="array",
     *                          @OA\Items(type="string")
     *                  ),
     *                  @OA\Property (
     *                          property="isActive",
     *                          description="User active status",
     *                          type="boolean"
     *                  ),
     *           )
     *      )
     * )
     * @OA\Tag(name="User")
     * @AnnotationSecurity(name="Authorization")
     */
    public function edit(Request $request, UserPasswordHasherInterface $userPasswordHasher, User $user, int $id): JsonResponse
    {
        try {
            if (!$this->checkContentType($request->headers->get('content-type'))) {
                return $this->json(ReplyUtils::failure(['message' => 'Content-type must be application/json!']));
            }

            if (($checkAuthorisation = $this->checkUserAuthorisation($this->getUser()->getId())) && !$checkAuthorisation['status']) {
                return $this->json($checkAuthorisation, 403);
            }

            $postedData = json_decode($request->getContent(), true);

            $isUserExistWithThatEmail = $this->userRepository->findOneBy(['email' => $postedData['email']]);
            if ($isUserExistWithThatEmail && $id !== $isUserExistWithThatEmail->getId()) {
                return $this->json(ReplyUtils::failure(['message' => 'This email is already exist!']));
            }

            $form = $this->createForm(UserType::class, $user);
            $form->submit($postedData);
            $form->handleRequest($request);

            if (!$form->isValid() && $errors = (string)$form->getErrors(true, false)) {
                return $this->json(ReplyUtils::failure(['message' => $errors]));
            }

            $user->setPassword($userPasswordHasher->hashPassword($user, $postedData['password']));
            $user->setRoles($postedData['roles']);
            $this->em->persist($user);
            $this->em->flush();

            return $this->json(ReplyUtils::success(['data' => $user->getId(), 'message' => 'success']));

        } catch (\Exception $exception) {
            //TODO: Log
            return $this->json(ReplyUtils::failure(['message' => $exception->getMessage()]), 500);
        }
    }

    /**
     * @param Request $request
     * @param int $userId
     * @return JsonResponse|void
     * @Route("/orders/{userId<^\d+$>}", name="fetch_user_orders", methods={"GET"})
     * @OA\Response (
     *     response="200",
     *     description="Fetchs an user orders",
     *     @OA\JsonContent(
     *           @OA\Property(property="status", type="boolean"),
     *           @OA\Property(property="data", type="object"),
     *           @OA\Property(property="message", type="string"),
     *        )
     * )
     * @OA\Tag(name="User")
     * @AnnotationSecurity(name="Authorization")
     */
    public function fetchUserOrders(Request $request, int $userId): JsonResponse
    {
        try {
            if (!$this->checkContentType($request->headers->get('content-type'))) {
                return $this->json(ReplyUtils::failure(['message' => 'Content-type must be application/json!']));
            }

            if (($checkAuthorisation = $this->checkUserAuthorisation($userId)) && !$checkAuthorisation['status']) {
                return $this->json($checkAuthorisation, 403);
            }

            $userOrders = $this->userRepository->fetchCustomerOrdersByCustomerId($userId, true);

            return $this->json(ReplyUtils::success(['data' => $userOrders, 'message' => 'success']));

        } catch (\Exception $exception) {
            //TODO: Log
            return $this->json(ReplyUtils::failure(['message' => $exception->getMessage()]), 500);
        }
    }

    /**
     * @param Request $request
     * @param int $userId
     * @return JsonResponse|void
     * @Route("/revenue/{userId<^\d+$>}", name="fetch_user_orders_revenue", methods={"GET"})
     * @OA\Response (
     *     response="200",
     *     description="Fetchs an user orders revenue",
     *     @OA\JsonContent(
     *           @OA\Property(property="status", type="boolean"),
     *           @OA\Property(property="data", type="object"),
     *           @OA\Property(property="message", type="string"),
     *        )
     * )
     * @OA\Tag(name="User")
     * @AnnotationSecurity(name="Authorization")
     */
    public function fetchUserOrdersRevenue(Request $request, int $userId): JsonResponse
    {
        try {
            if (!$this->checkContentType($request->headers->get('content-type'))) {
                return $this->json(ReplyUtils::failure(['message' => 'Content-type must be application/json!']));
            }

            if (($checkAuthorisation = $this->checkUserAuthorisation($userId)) && !$checkAuthorisation['status']) {
                return $this->json($checkAuthorisation, 403);
            }

            $userOrdersRevenue = $this->userRepository->fetchCustomerOrdersRevenueByCustomerId($userId, true);

            return $this->json(ReplyUtils::success(['data' => $userOrdersRevenue, 'message' => 'success']));

        } catch (\Exception $exception) {
            //TODO: Log
            return $this->json(ReplyUtils::failure(['message' => $exception->getMessage()]), 500);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse|void
     * @Route("/orders", name="fetch_users_orders", methods={"GET"})
     * @OA\Response (
     *     response="200",
     *     description="Fetchs all users orders",
     *     @OA\JsonContent(
     *           @OA\Property(property="status", type="boolean"),
     *           @OA\Property(property="data", type="object"),
     *           @OA\Property(property="message", type="string"),
     *        )
     * )
     * @OA\Tag(name="User")
     * @AnnotationSecurity(name="Authorization")
     */
    public function fetchUsersOrders(Request $request): JsonResponse
    {
        try {
            if (!$this->checkContentType($request->headers->get('content-type'))) {
                return $this->json(ReplyUtils::failure(['message' => 'Content-type must be application/json!']));
            }

            $userId = $this->getUser()->getId();
            if ($this->isGranted('ROLE_ADMIN')) {
                $userId = null;
            }

            $userOrders = $this->userRepository->fetchCustomerOrdersByCustomerId($userId, true);

            return $this->json(ReplyUtils::success(['data' => $userOrders, 'message' => 'success']));

        } catch (\Exception $exception) {
            //TODO: Log
            return $this->json(ReplyUtils::failure(['message' => $exception->getMessage()]), 500);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse|void
     * @Route("/revenues", name="fetch_users_orders_revenue", methods={"GET"})
     * @OA\Response (
     *     response="200",
     *     description="Fetchs all users orders revenue",
     *     @OA\JsonContent(
     *           @OA\Property(property="status", type="boolean"),
     *           @OA\Property(property="data", type="object"),
     *           @OA\Property(property="message", type="string"),
     *        )
     * )
     * @OA\Tag(name="User")
     * @AnnotationSecurity(name="Authorization")
     */
    public function fetchUsersOrdersRevenue(Request $request): JsonResponse
    {
        try {
            if (!$this->checkContentType($request->headers->get('content-type'))) {
                return $this->json(ReplyUtils::failure(['message' => 'Content-type must be application/json!']));
            }

            $userId = $this->getUser()->getId();
            if ($this->isGranted('ROLE_ADMIN')) {
                $userId = null;
            }

            $userOrdersRevenue = $this->userRepository->fetchCustomerOrdersRevenues($userId, true);

            return $this->json(ReplyUtils::success(['data' => $userOrdersRevenue, 'message' => 'success']));

        } catch (\Exception $exception) {
            //TODO: Log
            return $this->json(ReplyUtils::failure(['message' => $exception->getMessage()]), 500);
        }
    }
}
