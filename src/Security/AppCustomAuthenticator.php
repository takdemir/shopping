<?php

namespace App\Security;

use App\Repository\UserRepository;
use Firebase\JWT\JWT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class AppCustomAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'security_login';

    private UrlGeneratorInterface $urlGenerator;
    private UserRepository $userRepository;
    private UserPasswordHasherInterface $userPasswordHasher;
    private Security $security;

    public function __construct(
        UrlGeneratorInterface $urlGenerator,
        UserRepository $userRepository,
        UserPasswordHasherInterface $userPasswordHasher,
        Security $security
    )
    {
        $this->urlGenerator = $urlGenerator;
        $this->userRepository = $userRepository;
        $this->userPasswordHasher = $userPasswordHasher;
        $this->security = $security;
    }

    public function supports(Request $request): bool
    {

        $isAnonymousRoutes = ['security_login', 'security_logout', 'security_generate_token', 'api_v1_user_create'];

        if (($request->isMethod('POST') || $request->isMethod('GET')) && in_array($request->attributes->get('_route'), $isAnonymousRoutes)) {
            return false;
        }

        if ($this->security->getUser()) {
            return false;
        }

        return $this->checkApiToken($request);

    }

    public function authenticate(Request $request): PassportInterface
    {
        $email = $request->request->get('email', '');

        $request->getSession()->set(Security::LAST_USERNAME, $email);

        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($request->request->get('password', '')),
            [
                new CsrfTokenBadge('authenticate', $request->get('_csrf_token')),
            ]
        );
    }

    /**
     * @throws \Exception
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $tokenArray = $request->headers->all('x-api-token');
        if (is_array($tokenArray) && count($tokenArray) > 0 && $tokenArray[0]) {
            return null;
        }

        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        // For example:
        //return new RedirectResponse($this->urlGenerator->generate('some_route'));
        throw new \Exception('TODO: provide a valid redirect inside ' . __FILE__);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        $tokenArray = $request->headers->all('x-api-token');

        if (is_array($tokenArray) && count($tokenArray) > 0 && $tokenArray[0]) {
            return new JsonResponse(['success' => false, 'data' => [], 'message' => 'Token is invalid'], 403);
        }

        if ($request->hasSession()) {
            $request->getSession()->set(Security::AUTHENTICATION_ERROR, $exception);
        }

        $url = $this->getLoginUrl($request);

        return new RedirectResponse($url);
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }

    private function checkApiToken(Request $request): bool
    {
        try {
            $tokenArray = $request->headers->all('x-api-token');

            if (is_array($tokenArray) && count($tokenArray) > 0 && $tokenArray[0]) {
                $token = $tokenArray[0];
                $jwtSecretKey = $_ENV['JWT_SECRET_KEY'];
                $decodeJWT = JWT::decode($token, $jwtSecretKey, ['HS256']);
                $decodedToken = (array)$decodeJWT;

                if (!array_key_exists('email', $decodedToken) || !filter_var($decodedToken['email'], FILTER_VALIDATE_EMAIL)) {
                    return true;
                }

                $user = $this->userRepository->findOneBy(['email' => $decodedToken['email'], 'isActive' => true]);

                if (!$user) {
                    return true;
                }
                //new PreAuthenticatedToken($user, $token, $jwtSecretKey);
                return false;
            }
            return true;
        } catch (\Exception $exception) {
            //TODO: Log
            echo $exception->getMessage();
            return true;
        }

    }
}
