<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Exception\WorldRegionNotFoundException;
use FrankProjects\UltimateWarfare\Repository\GameUnitTypeRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionRepository;
use FrankProjects\UltimateWarfare\Service\Action\ConstructionActionService;
use FrankProjects\UltimateWarfare\Service\Action\RegionActionService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class RegionController extends BaseGameController
{
    private WorldRegionRepository $worldRegionRepository;
    private GameUnitTypeRepository $gameUnitTypeRepository;
    private ConstructionActionService $constructionActionService;
    private RegionActionService $regionActionService;

    public function __construct(
        WorldRegionRepository $worldRegionRepository,
        GameUnitTypeRepository $gameUnitTypeRepository,
        ConstructionActionService $constructionActionService,
        RegionActionService $regionActionService
    ) {
        $this->worldRegionRepository = $worldRegionRepository;
        $this->gameUnitTypeRepository = $gameUnitTypeRepository;
        $this->constructionActionService = $constructionActionService;
        $this->regionActionService = $regionActionService;
    }

    public function buy(Request $request, int $regionId): Response
    {
        $player = $this->getPlayer();
        $worldRegion = null;

        try {
            $worldRegion = $this->regionActionService->getWorldRegionByIdAndWorld($regionId, $player->getWorld());

            if ($request->isMethod(Request::METHOD_POST)) {
                $this->regionActionService->buyWorldRegion($regionId, $this->getPlayer());
                $this->addFlash('success', 'You have bought a Region!');
                return $this->redirectToRoute('Game/World/Region', ['regionId' => $worldRegion->getId()]);
            }
        } catch (WorldRegionNotFoundException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('Game/RegionList', [], 302);
        } catch (Throwable $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->render(
            'game/region/buy.html.twig',
            [
                'region' => $worldRegion,
                'player' => $player,
                'price' => $player->getRegionPrice()
            ]
        );
    }

    public function region(int $regionId): Response
    {
        $player = $this->getPlayer();

        try {
            $worldRegion = $this->regionActionService->getWorldRegionByIdAndWorld($regionId, $player->getWorld());
        } catch (WorldRegionNotFoundException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('Game/RegionList', [], 302);
        }

        $gameUnitTypes = $this->gameUnitTypeRepository->findAll();
        $gameUnitsData = $this->worldRegionRepository->getWorldGameUnitSumByWorldRegion($worldRegion);

        return $this->render(
            'game/region.html.twig',
            [
                'region' => $worldRegion,
                'player' => $player,
                'previousRegion' => $this->worldRegionRepository->getPreviousWorldRegionForPlayer($regionId, $player),
                'nextRegion' => $this->worldRegionRepository->getNextWorldRegionForPlayer($regionId, $player),
                'gameUnitTypes' => $gameUnitTypes,
                'gameUnitsData' => $gameUnitsData
            ]
        );
    }

    public function regionList(): Response
    {
        /**
         * XXX TODO: Add sorting support (by building space, population, buildings, units)
         */
        $player = $this->getPlayer();
        $regions = $player->getWorldRegions();

        // GameUnitType 1 = Buildings
        $gameUnitType = $this->gameUnitTypeRepository->find(1);

        foreach ($regions as $region) {
            $region->buildingsInConstruction = $this->constructionActionService->getCountGameUnitsInConstruction(
                $region,
                $gameUnitType
            );
            $region->buildings = $this->constructionActionService->getCountGameUnitsInWorldRegion(
                $region,
                $gameUnitType
            );
        }

        return $this->render(
            'game/regionList.html.twig',
            [
                'regions' => $regions,
                'player' => $player
            ]
        );
    }
}
