<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\UserSession;
use App\Repository\UserSessionRepository;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Session\Session;

class SessionService
{
    private string $cookieDomain;
    private string $cookieLifetime;
    private UserSessionRepository $userSessionRepository;

    public function __construct(
        string $cookieDomain,
        string $cookieLifetime,
        UserSessionRepository $userSessionRepository
    ) {
        $this->cookieDomain = $cookieDomain;
        $this->cookieLifetime = $cookieLifetime;
        $this->userSessionRepository = $userSessionRepository;
    }

    /**
     * Creates a session `Cookie` with the expiration date refreshed
     * @param Session $session
     * @return Cookie New `Cookie` to be sent in the `Response` headers
     */
    public function refreshCookie(Session $session): Cookie
    {
        return new Cookie(
            name: $session->getName(),
            value: $session->getId(),
            expire: time() + $this->cookieLifetime,
            path: '/',
            httpOnly: true,
            sameSite: 'lax',
            domain: $this->cookieDomain
        );       
    }

    /**
     * Creates a session `UserSession` with the expiration date updated
     * @param Session $session
     * @param User $user The user entity to take if this entity is a new one
     * @return UserSession|null
     */
    public function refreshUserSession(Session $session, User $user): ?UserSession
    {
        $userSession = $this->userSessionRepository->findOneBy(['sessionId' => $session->getId()]) ?? new UserSession();

        $userSession->setUser($userSession?->getUser() ?? $user);
        $userSession->setSessionId($session->getId());
        $userSession->setDateCreated($userSession?->getDateCreated() ?? new \DateTime());
        $userSession->setDateExpires(new \DateTime(sprintf('+%s seconds', $this->cookieLifetime)));

        return $userSession;
    }
}
