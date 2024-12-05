<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Repository\ResearchPlayerRepository;
use FrankProjects\UltimateWarfare\Repository\ResearchRepository;
use FrankProjects\UltimateWarfare\Service\Action\ResearchActionService;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class ResearchController extends BaseGameController
{
    private ResearchRepository $researchRepository;
    private ResearchPlayerRepository $researchPlayerRepository;
    private ResearchActionService $researchActionService;

    public function __construct(
        ResearchRepository $researchRepository,
        ResearchPlayerRepository $researchPlayerRepository,
        ResearchActionService $researchActionService
    ) {
        $this->researchRepository = $researchRepository;
        $this->researchPlayerRepository = $researchPlayerRepository;
        $this->researchActionService = $researchActionService;
    }

    public function research(): Response
    {
        $player = $this->getPlayer();
        $ongoingResearch = $this->researchRepository->findOngoingByPlayer($player);
        $unresearched = $this->researchRepository->findUnresearchedByPlayer($player);
        $researchPlayerRecords = $player->getPlayerResearch();
        $researchDataArray = [];
        $researchPlayerArray = [];
        foreach ($researchPlayerRecords as $researchPlayer) {
            if ($researchPlayer->getActive()) {
                $researchPlayerArray[] = $researchPlayer->getResearch()->getId();
            }
        }

        // XXX TODO: Make better code
        foreach ($unresearched as $key => $notResearched) {
            $researchDataArray[$key]['needs']['done'] = [];
            $researchDataArray[$key]['needs']['notDone'] = [];
            $researchDataArray[$key]['research'] = $notResearched;

            foreach ($notResearched->getResearchNeeds() as $researchNeed) {
                if (in_array($researchNeed->getRequiredResearch()->getId(), $researchPlayerArray, true)) {
                    $researchDataArray[$key]['needs']['done'][] = $researchNeed;
                } else {
                    $researchDataArray[$key]['needs']['notDone'][] = $researchNeed;
                }
            }

            if (count($researchDataArray[$key]['needs']['notDone']) > 0) {
                unset($researchDataArray[$key]);
            }
        }

        return $this->render(
            'game/research.html.twig',
            [
                'player' => $player,
                'ongoingResearch' => $ongoingResearch,
                'researchDataArray' => $researchDataArray
            ]
        );
    }

    public function history(): Response
    {
        $player = $this->getPlayer();
        $finishedResearch = $this->researchPlayerRepository->findFinishedByPlayer($player);

        return $this->render(
            'game/researchHistory.html.twig',
            [
                'player' => $player,
                'finishedResearch' => $finishedResearch
            ]
        );
    }

    public function performResearch(int $researchId): Response
    {
        try {
            $this->researchActionService->performResearch($researchId, $this->getPlayer());
            $this->addFlash('success', 'Successfully started a new research project!');
        } catch (Throwable $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('Game/Research');
    }

    public function performCancel(int $researchId): Response
    {
        try {
            $this->researchActionService->performCancel($researchId, $this->getPlayer());
            $this->addFlash('success', 'Successfully cancelled your research project!');
        } catch (Throwable $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('Game/Research');
    }
}
