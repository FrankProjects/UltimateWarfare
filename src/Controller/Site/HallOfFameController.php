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

        return $this->render('site/hallOfFame.html.twig', [
            'history' => $history
        ]);
    }

    public function round(int $worldId, int $round): Response
    {
        /**
         * XXX TODO: order by
         */
        $federations = $this->historyFederationRepository->findByWorldAndRound($worldId, $round);
        $players = $this->historyPlayerRepository->findByWorldAndRound($worldId, $round);

        return $this->render('site/hallOfFameRound.html.twig', [
            'federations' => $federations,
            'players' => $players
        ]);
    }
}
