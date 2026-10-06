<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Site;

use FrankProjects\UltimateWarfare\Service\CaptchaGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

final class CaptchaController extends AbstractController
{
    public function __construct(
        private readonly CaptchaGenerator $captchaGenerator
    ) {
    }

    public function captcha(): Response
    {
        $imageData = $this->captchaGenerator->generateImage();

        return new Response($imageData, Response::HTTP_OK, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ]);
    }
}
