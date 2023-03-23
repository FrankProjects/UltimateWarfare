<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Site;

use FrankProjects\UltimateWarfare\Controller\BaseController;
use FrankProjects\UltimateWarfare\Repository\GameNewsRepository;
use Symfony\Component\HttpFoundation\Response;

final class IndexController extends BaseController
{
    private GameNewsRepository $gameNewsRepository;

    public function __construct(
        GameNewsRepository $gameNewsRepository
    ) {
        $this->gameNewsRepository = $gameNewsRepository;
    }

    public function index(): Response
    {
        $gameNews = $this->gameNewsRepository->findActiveMainPageNews();

        return $this->render(
            'site/index.html.twig',
            [
                'gameNews' => $gameNews
            ]
        );
    }
}
