<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Site;

use FrankProjects\UltimateWarfare\Controller\BaseController;
use FrankProjects\UltimateWarfare\Repository\HistoryFederationRepository;
use FrankProjects\UltimateWarfare\Repository\HistoryPlayerRepository;
use FrankProjects\UltimateWarfare\Repository\HistoryRepository;
use Symfony\Component\HttpFoundation\Response;

final class HallOfFameController extends BaseController
{
    private HistoryRepository $historyRepository;
    private HistoryPlayerRepository $historyPlayerRepository;
    private HistoryFederationRepository $historyFederationRepository;

    public function __construct(
        HistoryRepository $historyRepository,
        HistoryPlayerRepository $historyPlayerRepository,
        HistoryFederationRepository $historyFederationRepository
    ) {
        $this->historyRepository = $historyRepository;
        $this->historyPlayerRepository = $historyPlayerRepository;
        $this->historyFederationRepository = $historyFederationRepository;
    }

    public function hallOfFame(): Response
    {
        $history = $this->historyRepository->findAll();

        return $this->render(
            'site/hallOfFame.html.twig',
            [
                'history' => $history
            ]
        );
    }

    public function world(int $worldId): Response
    {
        /**
         * XXX TODO: order by
         */
        $federations = $this->historyFederationRepository->findByWorld($worldId);
        $players = $this->historyPlayerRepository->findByWorld($worldId);

        return $this->render(
            'site/hallOfFameWorld.html.twig',
            [
                'federations' => $federations,
                'players' => $players
            ]
        );
    }
}
