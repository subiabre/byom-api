<?php

namespace App\EventSubscriber;

use App\Repository\UserRepository;
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
    private TokenStorageInterface $tokenStorage;
    private EntityManagerInterface $entityManager;

    public function __construct(
        SessionService $sessionService,
        UserRepository $userRepository,
        TokenStorageInterface $tokenStorageInterface,
        EntityManagerInterface $entityManagerInterface
    ) {
        $this->sessionService = $sessionService;
        $this->userRepository = $userRepository;
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
        $session = $event->getRequest()->getSession();
        $token = $this->tokenStorage->getToken();
        $user = $this->userRepository->findByUser($token?->getUser());

        if (!$session->isStarted() || !$token || !$user) return;

        $cookie = $this->sessionService->refreshCookie($session);
        $entity = $this->sessionService->refreshUserSession($session, $user);

        $entity->setUserAgent($event->getRequest()->headers->get('User-Agent'));

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $event->getResponse()->headers->setCookie($cookie);
    }
}
