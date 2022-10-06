<?php

namespace App\Security;

use App\Repository\UserTokenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class TokenAuthenticator extends AbstractAuthenticator
{
    private string $appSecret;
    private UserTokenRepository $userTokenRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        string $appSecret,
        UserTokenRepository $userTokenRepository,
        EntityManagerInterface $entityManagerInterface
    ) {
        $this->appSecret = $appSecret;
        $this->userTokenRepository = $userTokenRepository;
        $this->entityManager = $entityManagerInterface;
    }

    public function supports(Request $request): ?bool
    {
        if (
            $request->getPathInfo() === '/api/auth/token' &&
            $request->getMethod() === Request::METHOD_POST
        ) {
            return true;
        }

        return false;
    }

    public function authenticate(Request $request): Passport
    {
        try {
            $jwt = (array) JWT::decode(
                json_decode($request->getContent(), true)['token'],
                new Key($this->appSecret, 'HS256')
            );

            return new SelfValidatingPassport(
                new UserBadge($jwt['jti'], function($jti) {
                    $userToken = $this->userTokenRepository->find($jti);
                    $user = $userToken?->getUser();

                    if (!$user) throw new UserNotFoundException();

                    $this->entityManager->remove($userToken);
                    $this->entityManager->flush();

                    return $user;
                })
            );
        } catch (\Exception $e) {
            throw new CustomUserMessageAuthenticationException($e::class);
        }
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new JsonResponse(
            [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTrace()
            ],
            Response::HTTP_UNAUTHORIZED
        );
    }

//    public function start(Request $request, AuthenticationException $authException = null): Response
//    {
//        /*
//         * If you would like this class to control what happens when an anonymous user accesses a
//         * protected page (e.g. redirect to /login), uncomment this method and make this class
//         * implement Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface.
//         *
//         * For more details, see https://symfony.com/doc/current/security/experimental_authenticators.html#configuring-the-authentication-entry-point
//         */
//    }
}
