<?php


namespace App\Security;

use App\Repository\UserRepository;
use Firebase\JWT\JWT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class TokenAuthenticator extends AbstractAuthenticator
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Called on every request to decide if this authenticator should be
     * used for the request. Returning `false` will cause this authenticator
     * to be skipped.
     */
    public function supports(Request $request): bool
    {
        $tokenNotNeededRoutes = ['security_generate_token', 'app.swagger_ui', 'api_v1_user_create'];
        if (in_array($request->get('_route'), $tokenNotNeededRoutes)) {
            return false;
        }

        if (!$request->headers->has('x-api-token')) {
            throw new AuthenticationException("Token not found in header");
        }

        return true;
    }

    public function authenticate(Request $request): PassportInterface
    {
        $token = $request->headers->get('x-api-token');
        $jwtSecretKey = $_ENV['JWT_SECRET_KEY'];
        $decodeJWT = JWT::decode($token, $jwtSecretKey, ['HS256']);
        $decodedToken = (array)$decodeJWT;
        $userBadge = new UserBadge($decodedToken['email']);
        $passport = new SelfValidatingPassport($userBadge, []);
        $passport->setAttribute('scope', $decodedToken['email']);
        return $passport;
    }

    /**
     * Called on every request. Return whatever credentials you want to
     * be passed to getUser() as $credentials.
     */
    public function getCredentials(Request $request): array
    {
        $token = $request->headers->get('x-api-token');
        if (!$token) {
            throw new CustomUserMessageAuthenticationException('Authentication error. Token is needed.', [], 401);
        }
        $jwtSecretKey = $_ENV['JWT_SECRET_KEY'];
        $decodeJWT = JWT::decode($token, $jwtSecretKey, ['HS256']);
        $decodedToken = (array)$decodeJWT;
        if (!array_key_exists('email', $decodedToken) || !filter_var($decodedToken['email'], FILTER_VALIDATE_EMAIL)) {
            throw new CustomUserMessageAuthenticationException('Authentication error. Token does not contain valid credentials.', [], 401);
        }
        return $decodedToken;
    }

    public function getUser($credentials, UserProviderInterface $userProvider): ?UserInterface
    {
        if (null === $credentials) {
            // The token header was empty, authentication fails with HTTP Status
            // Code 401 "Unauthorized"
            return null;
        }

        // The user identifier in this case is the apiToken, see the key `property`
        // of `your_db_provider` in `security.yaml`.
        // If this returns a user, checkCredentials() is called next:
        return $this->userRepository->findOneBy(['email' => $credentials['email'], 'isActive' => true]);
    }

    public function checkCredentials($credentials, UserInterface $user): bool
    {
        // Check credentials - e.g. make sure the password is valid.
        // In case of an API token, no credential check is needed.

        // Return `true` to cause authentication success
        return true;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $providerKey): ?Response
    {
        // on success, let the request continue
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $data = [
            // you may want to customize or obfuscate the message first
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData())

            // or to translate this message
            // $this->translator->trans($exception->getMessageKey(), $exception->getMessageData())
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Called when authentication is needed, but it's not sent
     */
    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        $data = [
            // TODO: you might translate this message
            'message' => 'ApiToken is needed for authentication in header with x-api-token key'
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    public function supportsRememberMe(): bool
    {
        return false;
    }
}