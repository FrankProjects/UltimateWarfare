<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Admin;

use FrankProjects\UltimateWarfare\Service\MultiAccountDetectionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

final class MultiAccountController extends AbstractController
{
    public function list(MultiAccountDetectionService $multiAccountDetectionService): Response
    {
        return $this->render(
            'admin/multiAccount/list.html.twig',
            [
                'pairs' => $multiAccountDetectionService->findPairs(),
            ]
        );
    }
}
