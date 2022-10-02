<?php

namespace App\Controller;

use ApiPlatform\Api\IriConverterInterface;
use ApiPlatform\Symfony\Routing\IriConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class AuthController extends AbstractController
{
    private IriConverter $iriConverter;

    public function __construct(IriConverterInterface $iriConverterInterface)
    {
        $this->iriConverter = $iriConverterInterface;
    }

    #[Route('/auth', name: 'app_auth', methods: ['POST'])]
    public function index(): Response
    {
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return new JsonResponse(
                [
                    'error' => 'Invalid login request: check that the Content-Type header is "application/json".'
                ],
                Response::HTTP_BAD_REQUEST
            );
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
