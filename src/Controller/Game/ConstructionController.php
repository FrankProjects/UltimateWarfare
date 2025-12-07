<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Entity\GameUnitType;
use FrankProjects\UltimateWarfare\Exception\GameUnitTypeNotFoundException;
use FrankProjects\UltimateWarfare\Exception\WorldRegionNotFoundException;
use FrankProjects\UltimateWarfare\Repository\ConstructionRepository;
use FrankProjects\UltimateWarfare\Repository\GameUnitTypeRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionRepository;
use FrankProjects\UltimateWarfare\Service\Action\ConstructionActionService;
use FrankProjects\UltimateWarfare\Service\Action\RegionActionService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class ConstructionController extends BaseGameController
{
    private ConstructionRepository $constructionRepository;
    private GameUnitTypeRepository $gameUnitTypeRepository;
    private WorldRegionRepository $worldRegionRepository;
    private ConstructionActionService $constructionActionService;
    private RegionActionService $regionActionService;

    public function __construct(
        ConstructionRepository $constructionRepository,
        GameUnitTypeRepository $gameUnitTypeRepository,
        WorldRegionRepository $worldRegionRepository,
        ConstructionActionService $constructionActionService,
        RegionActionService $regionActionService
    ) {
        $this->constructionRepository = $constructionRepository;
        $this->gameUnitTypeRepository = $gameUnitTypeRepository;
        $this->worldRegionRepository = $worldRegionRepository;
        $this->constructionActionService = $constructionActionService;
        $this->regionActionService = $regionActionService;
    }

    public function construction(int $type): Response
    {
        $gameUnitTypes = $this->gameUnitTypeRepository->findAll();
        try {
            $gameUnitType = $this->gameUnitTypeRepository->find($type);
        } catch (GameUnitTypeNotFoundException $e) {
            $constructionData = $this->constructionRepository->getGameUnitConstructionSumByPlayer($this->getPlayer());
            return $this->render(
                'game/constructionSummary.html.twig',
                [
                    'player' => $this->getPlayer(),
                    'gameUnitTypes' => $gameUnitTypes,
                    'constructionData' => $constructionData
                ]
            );
        }

        $constructions = $this->constructionRepository->findByPlayerAndGameUnitType($this->getPlayer(), $gameUnitType);

        return $this->render(
            'game/construction.html.twig',
            [
                'player' => $this->getPlayer(),
                'constructions' => $constructions,
                'gameUnitType' => $gameUnitType,
                'gameUnitTypes' => $gameUnitTypes,
            ]
        );
    }

    public function constructGameUnits(Request $request, int $regionId, int $gameUnitTypeId): Response
    {
        /**
         * XXX TODO: Fix unit info page
         * XXX TODO: Fix buildtime to human readable format
         */
        try {
            $worldRegion = $this->regionActionService->getWorldRegionByIdAndPlayer($regionId, $this->getPlayer());
        } catch (WorldRegionNotFoundException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('Game/RegionList', [], 302);
        }

        try {
            $gameUnitType = $this->gameUnitTypeRepository->find($gameUnitTypeId);
        } catch (GameUnitTypeNotFoundException $e) {
            $this->addFlash('error', 'Unknown GameUnitType!');
            return $this->redirectToRoute('Game/World/Region', ['regionId' => $worldRegion->getId()], 302);
        }

        if ($request->isMethod(Request::METHOD_POST)) {
            try {
                /** @var array<int, string> $construct */
                $construct = $request->request->all('construct');
                $this->constructionActionService->constructGameUnits(
                    $worldRegion,
                    $this->getPlayer(),
                    $gameUnitType,
                    $construct
                );
                $this->addConstructGameUnitsFlash($gameUnitType);
            } catch (Throwable $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render(
            'game/region/constructGameUnits.html.twig',
            [
                'region' => $worldRegion,
                'player' => $this->getPlayer(),
                'spaceLeft' => $this->constructionActionService->getBuildingSpaceLeft($gameUnitType, $worldRegion),
                'gameUnitType' => $gameUnitType,
                'gameUnitTypes' => $this->gameUnitTypeRepository->findAll(),
                'gameUnitData' => $this->worldRegionRepository->getWorldGameUnitSumByWorldRegion($worldRegion),
                'constructionData' => $this->constructionRepository->getGameUnitConstructionSumByWorldRegion(
                    $worldRegion
                )
            ]
        );
    }

    private function addConstructGameUnitsFlash(GameUnitType $gameUnitType): void
    {
        /**
         * XXX TODO: Refactor to show what game units are being built/trained
         */
        if ($gameUnitType->getId() === GameUnitType::GAME_UNIT_TYPE_UNITS) {
            $this->addFlash('success', 'New units are now being trained!');
        } else {
            $this->addFlash('success', 'New buildings are now being built!');
        }
    }

    public function removeGameUnits(Request $request, int $regionId, int $gameUnitTypeId): Response
    {
        try {
            $worldRegion = $this->regionActionService->getWorldRegionByIdAndPlayer($regionId, $this->getPlayer());
        } catch (WorldRegionNotFoundException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('Game/RegionList', [], 302);
        }

        try {
            $gameUnitType = $this->gameUnitTypeRepository->find($gameUnitTypeId);
        } catch (GameUnitTypeNotFoundException) {
            return $this->redirectToRoute('Game/World/Region', ['regionId' => $worldRegion->getId()], 302);
        }

        if ($request->isMethod(Request::METHOD_POST)) {
            try {
                /** @var array<int, string> $destroy */
                $destroy = $request->request->all('destroy');
                $this->constructionActionService->removeGameUnits(
                    $worldRegion,
                    $this->getPlayer(),
                    $gameUnitType,
                    $destroy
                );
                $this->addRemoveGameUnitsFlash($gameUnitType);
            } catch (Throwable $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render(
            'game/region/removeGameUnits.html.twig',
            [
                'region' => $worldRegion,
                'player' => $this->getPlayer(),
                'gameUnitType' => $gameUnitType,
                'gameUnitTypes' => $this->gameUnitTypeRepository->findAll(),
                'gameUnitData' => $this->worldRegionRepository->getWorldGameUnitSumByWorldRegion($worldRegion),
            ]
        );
    }

    private function addRemoveGameUnitsFlash(GameUnitType $gameUnitType): void
    {
        /**
         * XXX TODO: Refactor to show what game units are being destroyed/disbanded
         */
        if ($gameUnitType->getId() === GameUnitType::GAME_UNIT_TYPE_UNITS) {
            $this->addFlash('success', "You have disbanded units!");
        } else {
            $this->addFlash('success', "You have destroyed buildings!");
        }
    }

    public function cancel(int $constructionId): RedirectResponse
    {
        try {
            $this->constructionActionService->cancelConstruction($this->getPlayer(), $constructionId);
            $this->addFlash('success', 'Successfully cancelled construction queue!');
        } catch (Throwable $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('Game/Construction', [], 302);
    }
}
