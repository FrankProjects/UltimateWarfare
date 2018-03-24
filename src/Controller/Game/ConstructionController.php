<?php

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Entity\Player;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ConstructionController extends BaseGameController
{
    /**
     * @param Request $request
     * @param int $type
     * @return Response
     */
    public function construction(Request $request, int $type): Response
    {
        $em = $this->getEm();
        $gameUnitType = $em->getRepository('Game:GameUnitType')
            ->find($type);

        $gameUnitTypes = $em->getRepository('Game:GameUnitType')
            ->findAll();

        if (!$gameUnitType) {
            $constructionData = $this->getConstructionData($this->getPlayer());
            return $this->render('game/constructionSummary.html.twig', [
                'player' => $this->getPlayer(),
                'gameUnitTypes' => $gameUnitTypes,
                'constructionData' => $constructionData
            ]);
        }

        $constructions = $em->getRepository('Game:Construction')
            ->findByGameUnitType($this->getPlayer(), $gameUnitType);

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
        $em = $this->getEm();
        $construction = $em->getRepository('Game:Construction')
            ->find($constructionId);

        if (!$construction) {
            $request->getSession()->getFlashBag()->add('error', "This construction queue doesn't exist!");
            return $this->redirectToRoute('Game/Construction', [], 302);
        }

        if ($construction->getPlayer()->getId() != $player->getId()) {
            $request->getSession()->getFlashBag()->add('error', "This is not your construction queue!");
            return $this->redirectToRoute('Game/Construction', [], 302);
        }

        $em->remove($construction);
        $em->flush();

        $request->getSession()->getFlashBag()->add('success', 'Succesfully cancelled construction queue!');
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
        $em = $this->getEm();
        $repository = $em->getRepository('Game:GameUnit');
        $gameUnits = $repository->findAll();
        $gameUnitArray = [];
        foreach ($gameUnits as $unit) {
            $gameUnitArray[$unit->getRowName()] = 0;
        }

        return $gameUnitArray;
    }
}
