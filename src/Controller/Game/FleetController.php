<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;
use FrankProjects\UltimateWarfare\Exception\WorldRegionNotFoundException;
use FrankProjects\UltimateWarfare\Repository\GameUnitTypeRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionRepository;
use FrankProjects\UltimateWarfare\Service\Action\FleetActionService;
use FrankProjects\UltimateWarfare\Service\Action\RegionActionService;
use FrankProjects\UltimateWarfare\Util\DistanceCalculator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class FleetController extends BaseGameController
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
     * FleetController constructor.
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
     * @return Response
     */
    public function fleetList(): Response
    {
        return $this->render('game/fleetList.html.twig', [
            'player' => $this->getPlayer()
        ]);
    }

    /**
     * @param int $fleetId
     * @return Response
     */
    public function recall(int $fleetId): Response
    {
        try {
            $this->fleetActionService->recall($fleetId, $this->getPlayer());
            $this->addFlash('success', 'You successfully recalled your troops!');
        } catch (Throwable $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->render('game/fleetList.html.twig', [
            'player' => $this->getPlayer()
        ]);
    }

    /**
     * @param int $fleetId
     * @return Response
     */
    public function reinforce(int $fleetId): Response
    {
        try {
            $this->fleetActionService->reinforce($fleetId, $this->getPlayer());
            $this->addFlash('success', 'You successfully reinforced your region!');
        } catch (Throwable $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->render('game/fleetList.html.twig', [
            'player' => $this->getPlayer()
        ]);
    }


    /**
     * @param Request $request
     * @param int $regionId
     * @return Response
     * @throws \Exception
     */
    public function sendGameUnits(Request $request, int $regionId): Response
    {
        $player = $this->getPlayer();

        try {
            $worldRegion = $this->regionActionService->getWorldRegionByIdAndPlayer($regionId, $player);
        } catch (WorldRegionNotFoundException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('Game/RegionList', [], 302);
        }

        if ($worldRegion->getPlayer()->getId() != $player->getId()) {
            return $this->redirectToRoute('Game/World/Region', ['regionId' => $worldRegion->getId()], 302);
        }

        $gameUnitType = $this->gameUnitTypeRepository->find(4);

        if ($request->getMethod() == 'POST') {
            $targetRegionId = intval($request->request->get('target', 0));
            $targetRegion = $this->regionActionService->getWorldRegionByIdAndPlayer($targetRegionId, $player);

            if ($targetRegion) {
                try {
                    $this->fleetActionService->sendGameUnits($worldRegion, $targetRegion, $player, $gameUnitType, $request->get('units'));
                    $this->addFlash('success', 'You successfully send units!');
                } catch (Throwable $e) {
                    $this->addFlash('error', $e->getMessage());
                }
            } else {
                $this->addFlash('error', "Target region does not exist.");
            }
        }

        $gameUnitsData = $this->worldRegionRepository->getWorldGameUnitSumByWorldRegion($worldRegion);
        $targetRegions = $this->getTargetWorldRegionData($player, $worldRegion);

        return $this->render('game/region/sendUnits.html.twig', [
            'region' => $worldRegion,
            'player' => $player,
            'gameUnitType' => $gameUnitType,
            'targetRegions' => $targetRegions,
            'gameUnitsData' => $gameUnitsData
        ]);
    }

    /**
     * @param Player $player
     * @param WorldRegion $region
     * @return WorldRegion[]
     */
    private function getTargetWorldRegionData(Player $player, WorldRegion $region): array
    {
        $distanceCalculator = new DistanceCalculator();

        $targetRegions = [];
        foreach ($player->getWorldRegions() as $worldRegion) {
            $distance = $distanceCalculator->calculateDistance(
                    $worldRegion->getX(),
                    $worldRegion->getY(),
                    $region->getX(),
                    $region->getY()
                ) * 100;

            $worldRegion->distance = $distance;
            $targetRegions[] = $worldRegion;
        }

        return $targetRegions;
    }
}
