<?php

namespace App\Controller;

use App\Entity\Music;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class PictureController extends AbstractController
{
    public function __invoke(Music $data): Response
    {
        $response = new Response(base64_decode($data->getPicture()['data']));
        $response->headers->set('Content-Type', $data->getPicture()['image_mime']);

        return $response;
    }
}
