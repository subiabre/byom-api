<?php

namespace App\Controller;

use App\Entity\Music;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class StreamController extends AbstractController
{
    public function __invoke(Music $data): Response
    {
        return new BinaryFileResponse($data->getStorage()->getPath());
    }
}
