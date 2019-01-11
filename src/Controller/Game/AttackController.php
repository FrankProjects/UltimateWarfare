<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Game;

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
    /**
     * @var WorldRegionRepository
     */
    private $worldRegionRepository;

    /**
     * @var GameUnitTypeRepository
     */
    private $gameUnitTypeRepository;

    /**
     * @var FleetActionService
     */
    private $fleetActionService;

    /**
     * @var RegionActionService
     */
    private $regionActionService;

    /**
     * RegionController constructor.
     *
     * @param WorldRegionRepository $worldRegionRepository
     * @param GameUnitTypeRepository $gameUnitTypeRepository
     * @param FleetActionService $fleetActionService
     * @param RegionActionService $regionActionService
     */
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

    /**
     * @param int $regionId
     * @return Response
     * @throws \Exception
     */
    public function attack(int $regionId): Response
    {
        $player = $this->getPlayer();
        $playerRegions = [];
        $worldRegion = null;

        try {
            $worldRegion = $this->regionActionService->getWorldRegionByIdAndPlayer($regionId, $player);
            $playerRegions = $this->regionActionService->getAttackFromWorldRegionList($worldRegion, $this->getPlayer());
        } catch (WorldRegionNotFoundException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('Game/RegionList', [], 302);
        } catch (Throwable $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->render('game/region/attackFrom.html.twig', [
            'region' => $worldRegion,
            'player' => $player,
            'mapUrl' => $this->getGameUser()->getMapDesign()->getUrl(),
            'playerRegions' => $playerRegions
        ]);
    }

    /**
     * @param Request $request
     * @param int $regionId
     * @param int $playerRegionId
     * @return Response
     * @throws \Exception
     */
    public function attackSelectGameUnits(Request $request, int $regionId, int $playerRegionId): Response
    {
        $player = $this->getPlayer();

        try {
            $worldRegion = $this->regionActionService->getWorldRegionByIdAndPlayer($regionId, $player);
        } catch (WorldRegionNotFoundException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('Game/RegionList', [], 302);
        }

        if ($worldRegion->getPlayer() === null) {
            $this->addFlash('error', "Can not attack nobody!");
            return $this->redirectToRoute('Game/World/Region', ['regionId' => $worldRegion->getId()], 302);
        }

        if ($worldRegion->getPlayer()->getId() == $player->getId()) {
            $this->addFlash('error', "Can not attack your own region!");
            return $this->redirectToRoute('Game/World/Region', ['regionId' => $worldRegion->getId()], 302);
        }

        try {
            $playerRegion = $this->regionActionService->getWorldRegionByIdAndPlayer($playerRegionId, $player);
        } catch (WorldRegionNotFoundException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('Game/RegionList', [], 302);
        }

        if ($playerRegion->getPlayer() === null || $playerRegion->getPlayer()->getId() != $player->getId()) {
            $this->addFlash('error', "You are not owner of this region!");
            return $this->redirectToRoute('Game/World/Region', ['regionId' => $playerRegion->getId()], 302);
        }

        $gameUnitType = $this->gameUnitTypeRepository->find(4);

        if ($request->getMethod() == 'POST' && $request->get('units') !== null) {
            $this->fleetActionService->sendGameUnits($playerRegion, $worldRegion, $player, $gameUnitType, $request->get('units'));
            return $this->redirectToRoute('Game/Fleets', [], 302);
        }

        $gameUnitsData = $this->worldRegionRepository->getWorldGameUnitSumByWorldRegion($playerRegion);

        return $this->render('game/region/attackSelectGameUnits.html.twig', [
            'region' => $worldRegion,
            'playerRegion' => $playerRegion,
            'player' => $player,
            'gameUnitType' => $gameUnitType,
            'gameUnitsData' => $gameUnitsData
        ]);
    }
}
