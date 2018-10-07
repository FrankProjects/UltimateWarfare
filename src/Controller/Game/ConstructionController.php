<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Repository\ConstructionRepository;
use FrankProjects\UltimateWarfare\Repository\GameUnitRepository;
use FrankProjects\UltimateWarfare\Repository\GameUnitTypeRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ConstructionController extends BaseGameController
{
    /**
     * @var ConstructionRepository
     */
    private $constructionRepository;

    /**
     * @var GameUnitRepository
     */
    private $gameUnitRepository;

    /**
     * @var GameUnitTypeRepository
     */
    private $gameUnitTypeRepository;

    /**
     * ConstructionController constructor.
     *
     * @param ConstructionRepository $constructionRepository
     * @param GameUnitRepository $gameUnitRepository
     * @param GameUnitTypeRepository $gameUnitTypeRepository
     */
    public function __construct(ConstructionRepository $constructionRepository, GameUnitRepository $gameUnitRepository, GameUnitTypeRepository $gameUnitTypeRepository)
    {
        $this->constructionRepository = $constructionRepository;
        $this->gameUnitRepository = $gameUnitRepository;
        $this->gameUnitTypeRepository = $gameUnitTypeRepository;
    }

    /**
     * @param Request $request
     * @param int $type
     * @return Response
     */
    public function construction(Request $request, int $type): Response
    {
        $gameUnitType = $this->gameUnitTypeRepository->find($type);
        $gameUnitTypes = $this->gameUnitTypeRepository->findAll();

        if (!$gameUnitType) {
            $constructionData = $this->getConstructionData($this->getPlayer());
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
     * @param Request $request
     * @param int $constructionId
     * @return RedirectResponse
     */
    public function cancel(Request $request, int $constructionId): RedirectResponse
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

        $this->constructionRepository->remove($construction);

        $this->addFlash('success', 'Succesfully cancelled construction queue!');
        return $this->redirectToRoute('Game/Construction', [], 302);
    }

    /**
     * @param Player $player
     * @return array
     */
    private function getConstructionData(Player $player): array
    {
        $gameUnitData = $this->getGameUnitFields();

        foreach ($player->getConstructions() as $data) {
            $gameUnitData[$data->getGameUnit()->getRowName()] += $data->getNumber();
        }

        return $gameUnitData;
    }

    /**
     * @return array
     */
    private function getGameUnitFields(): array
    {
        $gameUnits = $this->gameUnitRepository->findAll();
        $gameUnitArray = [];
        foreach ($gameUnits as $gameUnit) {
            $gameUnitArray[$gameUnit->getRowName()] = 0;
        }

        return $gameUnitArray;
    }
}
