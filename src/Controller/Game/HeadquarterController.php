<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Entity\GameUnitType;
use FrankProjects\UltimateWarfare\Repository\GameUnitTypeRepository;
use FrankProjects\UltimateWarfare\Repository\ReportRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionUnitRepository;
use Symfony\Component\HttpFoundation\Response;

final class HeadquarterController extends BaseGameController
{
    /**
     * @var GameUnitTypeRepository
     */
    private $gameUnitTypeRepository;

    /**
     * @var ReportRepository
     */
    private $reportRepository;

    /**
     * @var WorldRegionUnitRepository
     */
    private $worldRegionUnitRepository;

    /**
     * HeadquarterController constructor.
     *
     * @param GameUnitTypeRepository $gameUnitTypeRepository
     * @param ReportRepository $reportRepository
     * @param WorldRegionUnitRepository $worldRegionUnitRepository
     */
    public function __construct(
        GameUnitTypeRepository $gameUnitTypeRepository,
        ReportRepository $reportRepository,
        WorldRegionUnitRepository $worldRegionUnitRepository
    ) {
        $this->gameUnitTypeRepository = $gameUnitTypeRepository;
        $this->reportRepository = $reportRepository;
        $this->worldRegionUnitRepository = $worldRegionUnitRepository;
    }

    /**
     * @return Response
     */
    public function army(): Response
    {
        $gameUnitTypes = [
            $this->gameUnitTypeRepository->find(GameUnitType::GAME_UNIT_TYPE_UNITS),
            $this->gameUnitTypeRepository->find(GameUnitType::GAME_UNIT_TYPE_SPECIAL_UNITS)
        ];

        return $this->render('game/headquarter/army.html.twig', [
            'player' => $this->getPlayer(),
            'gameUnitTypes' => $gameUnitTypes,
            'gameUnitData' => $this->worldRegionUnitRepository->getGameUnitSumByPlayerAndGameUnitTypes($this->getPlayer(), $gameUnitTypes)
        ]);
    }

    /**
     * @return Response
     */
    public function headquarter(): Response
    {
        $reports = $this->reportRepository->findReports($this->getPlayer(), 10);

        return $this->render('game/headquarter.html.twig', [
            'player' => $this->getPlayer(),
            'reports' => $reports
        ]);
    }

    /**
     * @return Response
     */
    public function income(): Response
    {
        return $this->render('game/headquarter/income.html.twig', [
            'player' => $this->getPlayer(),
            'incomePop' => 0,
        ]);
    }

    /**
     * @return Response
     */
    public function infrastructure(): Response
    {
        $gameUnitTypes = [
            $this->gameUnitTypeRepository->find(GameUnitType::GAME_UNIT_TYPE_BUILDINGS),
            $this->gameUnitTypeRepository->find(GameUnitType::GAME_UNIT_TYPE_DEFENCE_BUILDINGS),
            $this->gameUnitTypeRepository->find(GameUnitType::GAME_UNIT_TYPE_SPECIAL_BUILDINGS)
        ];

        return $this->render('game/headquarter/infrastructure.html.twig', [
            'player' => $this->getPlayer(),
            'gameUnitTypes' => $gameUnitTypes,
            'gameUnitData' => $this->worldRegionUnitRepository->getGameUnitSumByPlayerAndGameUnitTypes($this->getPlayer(), $gameUnitTypes)
        ]);
    }
}
