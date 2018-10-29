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
    /**
     * @var HistoryRepository
     */
    private $historyRepository;

    /**
     * @var HistoryPlayerRepository
     */
    private $historyPlayerRepository;

    /**
     * @var HistoryFederationRepository
     */
    private $historyFederationRepository;

    /**
     * HallOfFameController constructor.
     *
     * @param HistoryRepository $historyRepository
     * @param HistoryPlayerRepository $historyPlayerRepository
     * @param HistoryFederationRepository $historyFederationRepository
     */
    public function __construct(
        HistoryRepository $historyRepository,
        HistoryPlayerRepository $historyPlayerRepository,
        HistoryFederationRepository $historyFederationRepository
    ) {
        $this->historyRepository = $historyRepository;
        $this->historyPlayerRepository = $historyPlayerRepository;
        $this->historyFederationRepository = $historyFederationRepository;
    }

    /**
     * @return Response
     */
    public function hallOfFame(): Response
    {
        $history = $this->historyRepository->findAll();

        return $this->render('site/hallOfFame.html.twig', [
            'history' => $history
        ]);
    }

    /**
     * XXX TODO: order by
     * @param int $worldId
     * @param int $round
     * @return Response
     */
    public function round(int $worldId, int $round): Response
    {
        $federations = $this->historyFederationRepository->findByWorldAndRound($worldId, $round);
        $players = $this->historyPlayerRepository->findByWorldAndRound($worldId, $round);

        return $this->render('site/hallOfFameRound.html.twig', [
            'federations' => $federations,
            'players' => $players
        ]);
    }
}
