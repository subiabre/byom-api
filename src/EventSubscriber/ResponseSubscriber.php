<?php

namespace App\EventSubscriber;

use App\Repository\UserRepository;
use App\Repository\UserSessionRepository;
use App\Service\SessionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ResponseSubscriber implements EventSubscriberInterface
{
    private SessionService $sessionService;
    private UserRepository $userRepository;
    private UserSessionRepository $userSessionRepository;
    private TokenStorageInterface $tokenStorage;
    private EntityManagerInterface $entityManager;

    public function __construct(
        SessionService $sessionService,
        UserRepository $userRepository,
        UserSessionRepository $userSessionRepository,
        TokenStorageInterface $tokenStorageInterface,
        EntityManagerInterface $entityManagerInterface
    ) {
        $this->sessionService = $sessionService;
        $this->userRepository = $userRepository;
        $this->userSessionRepository = $userSessionRepository;
        $this->tokenStorage = $tokenStorageInterface;
        $this->entityManager = $entityManagerInterface;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => [
                ['refreshSession', 0]
            ]
        ];
    }

    public function refreshSession(ResponseEvent $event)
    {
        try {
            $session = $event->getRequest()->getSession();
            $token = $this->tokenStorage->getToken();
            $user = $this->userRepository->findByUser($token?->getUser());
            $userSession = $this->userSessionRepository->findOneBySession($session);

            if (!$session->isStarted() || !$token || !$user || !$userSession) return;

            $cookie = $this->sessionService->refreshCookie($session);
            $entity = $this->sessionService->refreshUserSession($userSession);

            $this->entityManager->persist($entity);
            $this->entityManager->flush();

            $event->getResponse()->headers->setCookie($cookie);
        } catch (\Throwable $th) {
            return;
        }
    }
}
