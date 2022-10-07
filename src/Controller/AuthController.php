<?php

namespace App\Controller;

use ApiPlatform\Api\IriConverterInterface;
use ApiPlatform\Symfony\Routing\IriConverter;
use App\Entity\UserToken;
use App\Repository\UserRepository;
use App\Service\Snowflake\SnowflakeService;
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
    private SnowflakeService $snowflakeService;
    private EntityManagerInterface $entityManager;

    public function __construct(
        string $appSecret,
        IriConverterInterface $iriConverterInterface,
        UserRepository $userRepository,
        SnowflakeService $snowflakeService,
        EntityManagerInterface $entityManagerInterface,
    ) {
        $this->appSecret = $appSecret;
        $this->iriConverter = $iriConverterInterface;
        $this->userRepository = $userRepository;
        $this->snowflakeService = $snowflakeService;
        $this->entityManager = $entityManagerInterface;
    }

    private function error(string $message): Response
    {
        return new JsonResponse(
            [ 'error' => $message ],
            Response::HTTP_BAD_REQUEST
        );
    }

    #[Route('', name: 'app_auth', methods: ['GET'])]
    public function read(): Response
    {
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->error('No Authentication key found in request.');
        }

        return new Response(
            null,
            Response::HTTP_NO_CONTENT,
            [
                'Location' => $this->iriConverter->getIriFromResource($this->getUser())
            ]
        );
    }

    #[Route('', name: 'app_auth_login', methods: ['POST'])]
    public function login(): Response
    {
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->error('Invalid login request: check that the Content-Type header is "application/json".');
        }

        return new Response(
            null,
            Response::HTTP_NO_CONTENT,
            [
                'Location' => $this->iriConverter->getIriFromResource($this->getUser())
            ]
        );
    }

    #[Route('/token', name: 'app_auth_token', methods: ['GET'])]
    public function token(): Response
    {
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->error('No Authentication key found in request.');
        }

        $user = $this->userRepository->findByUser($this->getUser());

        foreach ($user->getUserTokens() as $token) {
            $this->entityManager->remove($token);
        }
        $this->entityManager->flush();

        $token = new UserToken();
        $token->setUser($user);
        $token->setId($this->snowflakeService->generateId());
        $token->setToken(JWT::encode([
            'jti' => $token->getId(),
            'exp' => (new \DateTime())->add(new \DateInterval('P1D'))->getTimestamp(),
        ], $this->appSecret, 'HS256'));

        $this->entityManager->persist($token);
        $this->entityManager->flush();

        return new JsonResponse(
            [ 'token' => $token->getToken() ],
            Response::HTTP_CREATED
        );
    }

    #[Route('/token', name: 'app_auth_token_login', methods: ['POST'])]
    public function tokenLogin(Request $request): Response
    {
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->error('No Authentication key found in request.');
        }

        return new Response(
            null,
            Response::HTTP_NO_CONTENT,
            [
                'Location' => $this->iriConverter->getIriFromResource($this->getUser())
            ]
        );
    }
}
