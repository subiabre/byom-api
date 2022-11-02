<?php

namespace App\Controller;

use ApiPlatform\Api\IriConverterInterface;
use ApiPlatform\Symfony\Routing\IriConverter;
use App\Entity\UserSession;
use App\Repository\UserRepository;
use App\Repository\UserSessionRepository;
use App\Service\SessionService;
use Doctrine\ORM\EntityManagerInterface;
use Firebase\JWT\JWT;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/auth')]
class AuthController extends AbstractController
{
    private string $appSecret;
    private IriConverter $iriConverter;
    private UserRepository $userRepository;
    private SessionService $sessionService;
    private UserSessionRepository $userSessionRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        string $appSecret,
        IriConverterInterface $iriConverterInterface,
        UserRepository $userRepository,
        SessionService $sessionService,
        UserSessionRepository $userSessionRepository,
        EntityManagerInterface $entityManagerInterface
    ) {
        $this->appSecret = $appSecret;
        $this->iriConverter = $iriConverterInterface;
        $this->userRepository = $userRepository;
        $this->sessionService = $sessionService;
        $this->userSessionRepository = $userSessionRepository;
        $this->entityManager = $entityManagerInterface;
    }

    private function error(string $message): Response
    {
        return new JsonResponse(
            [ 'error' => $message ],
            Response::HTTP_BAD_REQUEST
        );
    }

    #[Route('/user', name: 'app_auth_user', methods: ['GET'])]
    public function user(Request $request): Response
    {
        if (!$request->getSession()->isStarted()) $request->getSession()->start();
        
        $userSession = $this->userSessionRepository->findOneBySession($request->getSession());

        return new Response(
            null,
            Response::HTTP_NO_CONTENT,
            [
                'Location' => $this->iriConverter->getIriFromResource($userSession)
            ]
        );
    }

    #[Route('/user', name: 'app_auth_user_login', methods: ['POST'])]
    public function userLogin(Request $request): Response
    {
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->error('Invalid login request: check that the Content-Type header is "application/json".');
        }

        if (!$request->getSession()->isStarted()) $request->getSession()->start();

        $user = $this->userRepository->findByUser($this->getUser());

        $userSession = new UserSession;
        $userSession->setUser($user);
        $userSession->setSessionId($request->getSession()->getId());
        $userSession->setUserAgent($request->headers->get('User-Agent'));
        $userSession->setDateCreated(new \DateTime());
        $userSession = $this->sessionService->refreshUserSession($userSession);

        $this->entityManager->persist($userSession);
        $this->entityManager->flush();

        return new Response(
            null,
            Response::HTTP_NO_CONTENT,
            [
                'Location' => $this->iriConverter->getIriFromResource($userSession)
            ]
        );
    }

    #[Route('/token', name: 'app_auth_token', methods: ['PUT'])]
    public function token(): Response
    {
        $user = $this->userRepository->findByUser($this->getUser());
        $token = JWT::encode(
            [
                'user' => $user->getId(),
                'exp' => (new \DateTime())->add(new \DateInterval('P1D'))->getTimestamp(),
            ],
            $this->appSecret,
            'HS256'
        );

        return new JsonResponse(
            [ 'token' => $token ],
            Response::HTTP_CREATED
        );
    }

    #[Route('/token', name: 'app_auth_token_login', methods: ['POST'])]
    public function tokenLogin(Request $request): Response
    {
        return $this->userLogin($request);
    }
}
