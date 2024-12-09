<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Entity\GameUnitType;
use FrankProjects\UltimateWarfare\Exception\GameUnitTypeNotFoundException;
use FrankProjects\UltimateWarfare\Exception\WorldRegionNotFoundException;
use FrankProjects\UltimateWarfare\Repository\GameUnitTypeRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionRepository;
use FrankProjects\UltimateWarfare\Service\Action\FleetActionService;
use FrankProjects\UltimateWarfare\Service\Action\RegionActionService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class AttackController extends BaseGameController
{
    private WorldRegionRepository $worldRegionRepository;
    private GameUnitTypeRepository $gameUnitTypeRepository;
    private FleetActionService $fleetActionService;
    private RegionActionService $regionActionService;

    public function __construct(
        WorldRegionRepository $worldRegionRepository,
        GameUnitTypeRepository $gameUnitTypeRepository,
        FleetActionService $fleetActionService,
        RegionActionService $regionActionService
    ) {
        $this->worldRegionRepository = $worldRegionRepository;
        $this->gameUnitTypeRepository = $gameUnitTypeRepository;
        $this->fleetActionService = $fleetActionService;
        $this->regionActionService = $regionActionService;
    }

    public function attack(int $regionId): Response
    {
        $player = $this->getPlayer();
        $playerRegions = [];
        $worldRegion = null;

        try {
            $worldRegion = $this->regionActionService->getWorldRegionByIdAndWorld($regionId, $player->getWorld());
            $playerRegions = $this->regionActionService->getAttackFromWorldRegionList($worldRegion, $this->getPlayer());
        } catch (WorldRegionNotFoundException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('Game/RegionList', [], 302);
        } catch (Throwable $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->render(
            'game/region/attackFrom.html.twig',
            [
                'region' => $worldRegion,
                'player' => $player,
                'playerRegions' => $playerRegions
            ]
        );
    }

    public function attackSelectGameUnits(Request $request, int $regionId, int $playerRegionId): Response
    {
        $player = $this->getPlayer();

        try {
            $worldRegion = $this->regionActionService->getWorldRegionByIdAndWorld($regionId, $player->getWorld());
        } catch (WorldRegionNotFoundException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('Game/RegionList', [], 302);
        }

        if ($worldRegion->getPlayer() === null) {
            $this->addFlash('error', "Can not attack nobody!");
            return $this->redirectToRoute('Game/World/Region', ['regionId' => $worldRegion->getId()], 302);
        }

        if ($worldRegion->getPlayer()->getId() === $player->getId()) {
            $this->addFlash('error', "Can not attack your own region!");
            return $this->redirectToRoute('Game/World/Region', ['regionId' => $worldRegion->getId()], 302);
        }

        try {
            $playerRegion = $this->regionActionService->getWorldRegionByIdAndPlayer($playerRegionId, $player);
            $gameUnitType = $this->gameUnitTypeRepository->find(GameUnitType::GAME_UNIT_TYPE_UNITS);
        } catch (WorldRegionNotFoundException | GameUnitTypeNotFoundException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('Game/RegionList', [], 302);
        }

        if ($request->isMethod(Request::METHOD_POST) && $request->get('units') !== null) {
            /** @var array<int, string> $units */
            $units = $request->get('units');
            $this->fleetActionService->sendGameUnits(
                $playerRegion,
                $worldRegion,
                $player,
                $gameUnitType,
                $units
            );
            return $this->redirectToRoute('Game/Fleets', [], 302);
        }

        $gameUnitsData = $this->worldRegionRepository->getWorldGameUnitSumByWorldRegion($playerRegion);

        return $this->render(
            'game/region/attackSelectGameUnits.html.twig',
            [
                'region' => $worldRegion,
                'playerRegion' => $playerRegion,
                'player' => $player,
                'gameUnitType' => $gameUnitType,
                'gameUnitsData' => $gameUnitsData
            ]
        );
    }
}
