<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Service\SessionService;
use Doctrine\ORM\EntityManagerInterface;

class UserSessionStateProcessor implements ProcessorInterface
{
    private SessionService $sessionService;
    private EntityManagerInterface $entityManager;

    public function __construct(
        SessionService $sessionService,
        EntityManagerInterface $entityManagerInterface
    ) {
        $this->sessionService = $sessionService;
        $this->entityManager = $entityManagerInterface;
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): void
    {
        $this->sessionService->invalidateSession($data->getSessionId());

        $this->entityManager->remove($data);
        $this->entityManager->flush();
    }
}
