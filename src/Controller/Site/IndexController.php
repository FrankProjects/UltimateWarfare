<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Site;

use FrankProjects\UltimateWarfare\Controller\BaseController;
use FrankProjects\UltimateWarfare\Repository\GameNewsRepository;
use FrankProjects\UltimateWarfare\Repository\TopicRepository;
use Symfony\Component\HttpFoundation\Response;

final class IndexController extends BaseController
{
    private GameNewsRepository $gameNewsRepository;
    private TopicRepository $topicRepository;

    public function __construct(
        GameNewsRepository $gameNewsRepository,
        TopicRepository $topicRepository
    ) {
        $this->gameNewsRepository = $gameNewsRepository;
        $this->topicRepository = $topicRepository;
    }

    public function index(): Response
    {
        $gameNews = $this->gameNewsRepository->findActiveMainPageNews();
        $latestAnnouncements = $this->topicRepository->findLastAnnouncements(7);

        return $this->render(
            'site/index.html.twig',
            [
                'latestAnnouncements' => $latestAnnouncements,
                'gameNews' => $gameNews
            ]
        );
    }
}
