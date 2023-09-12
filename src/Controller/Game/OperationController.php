<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Entity\GameUnitType;
use FrankProjects\UltimateWarfare\Exception\GameUnitTypeNotFoundException;
use FrankProjects\UltimateWarfare\Exception\WorldRegionNotFoundException;
use FrankProjects\UltimateWarfare\Repository\GameUnitTypeRepository;
use FrankProjects\UltimateWarfare\Repository\OperationRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionRepository;
use FrankProjects\UltimateWarfare\Service\Action\RegionActionService;
use FrankProjects\UltimateWarfare\Service\OperationService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class OperationController extends BaseGameController
{
    private OperationRepository $operationRepository;
    private WorldRegionRepository $worldRegionRepository;
    private GameUnitTypeRepository $gameUnitTypeRepository;
    private RegionActionService $regionActionService;
    private OperationService $operationService;

    public function __construct(
        OperationRepository $operationRepository,
        WorldRegionRepository $worldRegionRepository,
        GameUnitTypeRepository $gameUnitTypeRepository,
        RegionActionService $regionActionService,
        OperationService $operationService
    ) {
        $this->operationRepository = $operationRepository;
        $this->worldRegionRepository = $worldRegionRepository;
        $this->gameUnitTypeRepository = $gameUnitTypeRepository;
        $this->regionActionService = $regionActionService;
        $this->operationService = $operationService;
    }

    /**
     * XXX TODO: Fix sorting for region selection table
     *
     * @param int $regionId
     * @param int $operationId
     * @return Response
     */
    public function selectWorldRegion(int $regionId, int $operationId): Response
    {
        $player = $this->getPlayer();
        $playerRegions = [];
        $worldRegion = null;

        try {
            $worldRegion = $this->regionActionService->getWorldRegionByIdAndWorld($regionId, $player->getWorld());
            $playerRegions = $this->regionActionService->getOperationAttackFromWorldRegionList($worldRegion, $this->getPlayer());
        } catch (WorldRegionNotFoundException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('Game/RegionList', [], 302);
        } catch (Throwable $e) {
            $this->addFlash('error', $e->getMessage());
        }

        $operation = $this->operationRepository->find($operationId);

        return $this->render(
            'game/operation/selectRegion.html.twig',
            [
                'region' => $worldRegion,
                'player' => $player,
                'playerRegions' => $playerRegions,
                'operation' => $operation
            ]
        );
    }

    public function selectGameUnits(Request $request, int $regionId, int $operationId, int $playerRegionId): Response
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

        $gameUnitsData = $this->worldRegionRepository->getWorldGameUnitSumByWorldRegion($playerRegion);
        $operation = $this->operationRepository->find($operationId);

        return $this->render(
            'game/operation/selectGameUnit.html.twig',
            [
                'region' => $worldRegion,
                'playerRegion' => $playerRegion,
                'player' => $player,
                'gameUnitsData' => $gameUnitsData,
                'operation' => $operation
            ]
        );
    }

    public function selectOperation(Request $request, int $regionId): Response
    {
        $player = $this->getPlayer();

        try {
            $worldRegion = $this->regionActionService->getWorldRegionByIdAndWorld($regionId, $player->getWorld());
            $gameUnitType = $this->gameUnitTypeRepository->find(GameUnitType::GAME_UNIT_TYPE_UNITS);
        } catch (WorldRegionNotFoundException | GameUnitTypeNotFoundException $e) {
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

        $operations = $this->operationRepository->findAvailableForPlayer($player);

        return $this->render(
            'game/operation/selectOperation.html.twig',
            [
                'region' => $worldRegion,
                'player' => $player,
                'operations' => $operations,
                'gameUnitType' => $gameUnitType
            ]
        );
    }

    public function executeOperation(Request $request, int $regionId, int $operationId, int $playerRegionId): Response
    {
        $operationResults = [];
        $player = $this->getPlayer();

        try {
            $worldRegion = $this->regionActionService->getWorldRegionByIdAndWorld($regionId, $player->getWorld());
        } catch (WorldRegionNotFoundException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('Game/RegionList', [], 302);
        }

        $operation = $this->operationRepository->find($operationId);

        try {
            $playerRegion = $this->regionActionService->getWorldRegionByIdAndPlayer($playerRegionId, $player);
        } catch (WorldRegionNotFoundException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('Game/RegionList', [], 302);
        }

        if ($request->isMethod(Request::METHOD_POST)) {
            try {
                $operationResults = $this->operationService->executeOperation(
                    $worldRegion,
                    $operation,
                    $playerRegion,
                    intval($request->get('amount'))
                );
                $this->addFlash('success', 'Successfully executed the operation!');
            } catch (Throwable $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render(
            'game/operation/executeOperation.html.twig',
            [
                'region' => $worldRegion,
                'player' => $player,
                'operation' => $operation,
                'operationResults' => $operationResults
            ]
        );
    }
}
