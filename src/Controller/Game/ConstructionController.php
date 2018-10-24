<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Exception\WorldRegionNotFoundException;
use FrankProjects\UltimateWarfare\Repository\ConstructionRepository;
use FrankProjects\UltimateWarfare\Repository\GameUnitTypeRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionRepository;
use FrankProjects\UltimateWarfare\Service\ConstructionActionService;
use FrankProjects\UltimateWarfare\Service\RegionActionService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class ConstructionController extends BaseGameController
{
    /**
     * @var ConstructionRepository
     */
    private $constructionRepository;

    /**
     * @var GameUnitTypeRepository
     */
    private $gameUnitTypeRepository;

    /**
     * @var WorldRegionRepository
     */
    private $worldRegionRepository;

    /**
     * @var ConstructionActionService
     */
    private $constructionActionService;

    /**
     * @var RegionActionService
     */
    private $regionActionService;

    /**
     * ConstructionController constructor.
     *
     * @param ConstructionRepository $constructionRepository
     * @param GameUnitTypeRepository $gameUnitTypeRepository
     * @param WorldRegionRepository $worldRegionRepository
     * @param ConstructionActionService $constructionActionService
     * @param RegionActionService $regionActionService
     */
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

    /**
     * @param int $type
     * @return Response
     */
    public function construction(int $type): Response
    {
        $gameUnitType = $this->gameUnitTypeRepository->find($type);
        $gameUnitTypes = $this->gameUnitTypeRepository->findAll();

        if (!$gameUnitType) {
            $constructionData = $this->constructionRepository->getGameUnitConstructionSumByPlayer($this->getPlayer());
            return $this->render('game/constructionSummary.html.twig', [
                'player' => $this->getPlayer(),
                'gameUnitTypes' => $gameUnitTypes,
                'constructionData' => $constructionData
            ]);
        }

        $constructions = $this->constructionRepository->findByPlayerAndGameUnitType($this->getPlayer(), $gameUnitType);

        return $this->render('game/construction.html.twig', [
            'player' => $this->getPlayer(),
            'constructions' => $constructions,
            'gameUnitType' => $gameUnitType,
            'gameUnitTypes' => $gameUnitTypes,
        ]);
    }

    /**
     * XXX TODO: Fix unit info page
     * XXX TODO: Fix buildtime to human readable format
     *
     * @param Request $request
     * @param int $regionId
     * @param int $gameUnitTypeId
     * @return Response
     * @throws \Exception
     */
    public function constructGameUnits(Request $request, int $regionId, int $gameUnitTypeId): Response
    {
        $player = $this->getPlayer();

        try {
            $worldRegion = $this->regionActionService->getWorldRegionByIdAndPlayer($regionId, $player);
        } catch (WorldRegionNotFoundException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('Game/RegionList', [], 302);
        }

        $gameUnitType = $this->gameUnitTypeRepository->find($gameUnitTypeId);

        if (!$gameUnitType) {
            $this->addFlash('error', 'Unknown GameUnitType!');
            return $this->redirectToRoute('Game/World/Region', ['regionId' => $worldRegion->getId()], 302);
        }

        if ($worldRegion->getPlayer()->getId() != $player->getId()) {
            $this->addFlash('error', 'This is not your region!');
            return $this->redirectToRoute('Game/World/Region', ['regionId' => $worldRegion->getId()], 302);
        }

        if ($request->isMethod('POST')) {
            $this->constructionActionService->constructGameUnits($worldRegion, $player, $gameUnitType, $request->get('construct'));

            // XXX TODO: Refactor to show what!
            if ($gameUnitType->getId() == 4) {
                $this->addFlash('success', 'New units are now being trained!');
            } else {
                $this->addFlash('success', 'New buildings are now being built!');
            }
        }

        $gameUnitTypes = $this->gameUnitTypeRepository->findAll();

        $gameUnitData = $this->worldRegionRepository->getWorldGameUnitSumByWorldRegion($worldRegion);
        $constructionData = $this->constructionRepository->getGameUnitConstructionSumByWorldRegion($worldRegion);

        return $this->render('game/region/constructGameUnits.html.twig', [
            'region' => $worldRegion,
            'player' => $player,
            'spaceLeft' => $this->constructionActionService->getBuildingSpaceLeft($gameUnitType, $worldRegion),
            'gameUnitType' => $gameUnitType,
            'gameUnitTypes' => $gameUnitTypes,
            'gameUnitData' => $gameUnitData,
            'constructionData' => $constructionData
        ]);
    }

    /**
     * @param Request $request
     * @param int $regionId
     * @param int $gameUnitTypeId
     * @return Response
     * @throws \Exception
     */
    public function removeGameUnits(Request $request, int $regionId, int $gameUnitTypeId): Response
    {
        $player = $this->getPlayer();

        try {
            $worldRegion = $this->regionActionService->getWorldRegionByIdAndPlayer($regionId, $player);
        } catch (WorldRegionNotFoundException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('Game/RegionList', [], 302);
        }

        $gameUnitType = $this->gameUnitTypeRepository->find($gameUnitTypeId);

        if (!$gameUnitType) {
            return $this->redirectToRoute('Game/World/Region', ['regionId' => $worldRegion->getId()], 302);
        }

        if ($worldRegion->getPlayer()->getId() != $player->getId()) {
            return $this->redirectToRoute('Game/World/Region', ['regionId' => $worldRegion->getId()], 302);
        }

        if ($request->isMethod('POST')) {
            $this->constructionActionService->removeGameUnits($worldRegion, $player, $gameUnitType, $request->get('destroy'));

            // XXX TODO: Refactor to show what!
            if ($gameUnitType->getId() == 4) {
                $this->addFlash('success', "You have disbanded units!");
            } else {
                $this->addFlash('success', "You have destroyed buildings!");
            }
        }

        $gameUnitTypes = $this->gameUnitTypeRepository->findAll();

        $gameUnitData = $this->worldRegionRepository->getWorldGameUnitSumByWorldRegion($worldRegion);

        return $this->render('game/region/removeGameUnits.html.twig', [
            'region' => $worldRegion,
            'player' => $player,
            'gameUnitType' => $gameUnitType,
            'gameUnitTypes' => $gameUnitTypes,
            'gameUnitData' => $gameUnitData,
        ]);
    }

    /**
     * @param int $constructionId
     * @return RedirectResponse
     */
    public function cancel(int $constructionId): RedirectResponse
    {
        $player = $this->getPlayer();
        $construction = $this->constructionRepository->find($constructionId);

        if (!$construction) {
            $this->addFlash('error', "This construction queue doesn't exist!");
            return $this->redirectToRoute('Game/Construction', [], 302);
        }

        if ($construction->getPlayer()->getId() != $player->getId()) {
            $this->addFlash('error', "This is not your construction queue!");
            return $this->redirectToRoute('Game/Construction', [], 302);
        }

        try {
            $this->constructionRepository->remove($construction);
            $this->addFlash('success', 'Successfully cancelled construction queue!');
        } catch (Throwable $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('Game/Construction', [], 302);
    }
}
