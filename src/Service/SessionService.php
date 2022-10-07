<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\UserSession;
use App\Repository\UserSessionRepository;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionUtils;

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
     * Invalidates a specific session without invalidating the currently active session
     * @param string $id The id of the session to be invalidated
     */
    public function invalidateSession(string $id): void
    {
        $session = new Session();
        $currentId = $session->getId();

        $session->save();
        $session->setId($id);
        $session->start();
        $session->invalidate();

        /**
         * Symfony\Component\HttpFoundation\Session\Session::invalidate() will set the session cookie as 'deleted'
         * So we avoid that because we don't really wan't to log out the user
         */
        SessionUtils::popSessionCookie($session->getName(), 'deleted');

        $session->save();
        $session->setId($currentId);
        $session->start();
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
     * @param User $user The user entity to take if this entity is a new one
     * @param Session $session
     * @return UserSession|null
     */
    public function refreshUserSession(User $user, Session $session): ?UserSession
    {
        $userSession = $this->userSessionRepository->findOneBy(['sessionId' => $session->getId()]) ?? new UserSession();

        $userSession->setUser($userSession?->getUser() ?? $user);
        $userSession->setSessionId($session->getId());
        $userSession->setDateCreated($userSession?->getDateCreated() ?? new \DateTime());
        $userSession->setDateExpires(new \DateTime(sprintf('+%s seconds', $this->cookieLifetime)));

        return $userSession;
    }
}
