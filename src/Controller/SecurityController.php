<?php

namespace App\Controller;

use App\Util\ReplyUtils;
use Doctrine\ORM\NonUniqueResultException;
use Firebase\JWT\JWT;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Class SecurityController
 * @package App\Controller
 */
class SecurityController extends AbstractController
{
    use BaseTrait;

    /**
     * @Route("/login", name="security_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="security_logout")
     */
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * @Route("/auth/generate-token", name="security_generate_token", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     * @throws NonUniqueResultException
     */
    public function generateNewToken(Request $request): JsonResponse
    {
        if (!$this->checkContentType($request->headers->get('content-type'))) {
            return $this->json(ReplyUtils::failure(['message' => 'Content-type must be application/json!']));
        }

        $postedData = json_decode($request->getContent(), true);

        if (!array_key_exists('email', $postedData)) {
            return $this->json(ReplyUtils::failure(['message' => 'Username parameter not found in request body!']));
        }

        if (!array_key_exists('password', $postedData)) {
            return $this->json(ReplyUtils::failure(['message' => 'Password parameter not found in request body!']));
        }

        $userName = $postedData['email'];
        $password = $postedData['password'];

        if (!filter_var($userName, FILTER_VALIDATE_EMAIL)) {
            return $this->json(ReplyUtils::failure(["Username must be your email!"]));
        }

        $user = $this->userRepository->findUserByUsernameAndPassword($userName, $password);

        if (!$user) {
            return $this->json(ReplyUtils::failure(['message' => 'No user found with that credentials!']), 403);
        }

        $jwtSecretKey = $_ENV['JWT_SECRET_KEY'];
        $payload = ['email' => $user->getEmail()];
        $jwtToken = JWT::encode($payload, $jwtSecretKey); // Expire time can be defined but not needed in that project

        return $this->json(ReplyUtils::failure(['data' => $jwtToken, 'message' => 'success']));
    }
}
