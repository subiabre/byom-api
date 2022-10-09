<?php

namespace App\Controller;

use ApiPlatform\Api\IriConverterInterface;
use ApiPlatform\Symfony\Routing\IriConverter;
use App\Repository\UserRepository;
use Firebase\JWT\JWT;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/auth')]
class AuthController extends AbstractController
{
    private string $appSecret;
    private IriConverter $iriConverter;
    private UserRepository $userRepository;

    public function __construct(
        string $appSecret,
        IriConverterInterface $iriConverterInterface,
        UserRepository $userRepository,
    ) {
        $this->appSecret = $appSecret;
        $this->iriConverter = $iriConverterInterface;
        $this->userRepository = $userRepository;
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

    #[Route('/token', name: 'app_auth_token', methods: ['PUT'])]
    public function token(): Response
    {
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->error('No Authentication key found in request.');
        }

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
}
