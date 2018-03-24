<?php

namespace FrankProjects\UltimateWarfare\Controller\Site;

use FrankProjects\UltimateWarfare\Controller\BaseController;
use FrankProjects\UltimateWarfare\Entity\GameNews;
use FrankProjects\UltimateWarfare\Entity\Topic;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class IndexController extends BaseController
{
    /**
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        $em = $this->getEm();
        $gameNews = $em->getRepository(GameNews::class)
            ->findActiveMainPageNews();

        $latestAnnouncements = $em->getRepository(Topic::class)
            ->findLastAnnouncements(7);

        return $this->render('site/index.html.twig', [
            'latestAnnouncements' => $latestAnnouncements,
            'gameNews' => $gameNews
        ]);
    }
}
