<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Repository\ResearchRepository;
use FrankProjects\UltimateWarfare\Service\Action\ResearchActionService;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class ResearchController extends BaseGameController
{
    /**
     * @var ResearchRepository
     */
    private $researchRepository;

    /**
     * @var ResearchActionService
     */
    private $researchActionService;

    /**
     * ResearchController
     *
     * @param ResearchRepository $researchRepository
     * @param ResearchActionService $researchActionService
     */
    public function __construct(
        ResearchRepository $researchRepository,
        ResearchActionService $researchActionService
    ) {
        $this->researchRepository = $researchRepository;
        $this->researchActionService = $researchActionService;
    }

    /**
     * @return Response
     */
    public function research(): Response
    {
        $player = $this->getPlayer();
        $ongoingResearch = $this->researchRepository->findOngoingByPlayer($player);
        $unresearched = $this->researchRepository->findUnresearchedByPlayer($player);
        $researchPlayerRecords = $player->getPlayerResearch();

        $researchPlayerArray = [];
        foreach ($researchPlayerRecords as $researchPlayer) {
            $researchPlayerArray[] = $researchPlayer->getResearch()->getId();
        }

        // XXX TODO: Make better code
        foreach ($unresearched as $key => $notResearched) {
            $unresearched[$key]->needs['done'] = [];
            $unresearched[$key]->needs['notDone'] = [];
            foreach ($notResearched->getResearchNeeds() as $researchNeed) {
                if (in_array($researchNeed->getRequiredResearch()->getId(), $researchPlayerArray)) {
                    $unresearched[$key]->needs['done'][] = $researchNeed;
                } else {
                    $unresearched[$key]->needs['notDone'][] = $researchNeed;
                }
            }
        }

        return $this->render('game/research.html.twig', [
            'player' => $player,
            'ongoingResearch' => $ongoingResearch,
            'unresearched' => $unresearched
        ]);
    }

    /**
     * @return Response
     */
    public function history(): Response
    {
        $player = $this->getPlayer();
        $finishedResearch = $this->researchRepository->findFinishedByPlayer($player);

        return $this->render('game/researchHistory.html.twig', [
            'player' => $player,
            'finishedResearch' => $finishedResearch
        ]);
    }

    /**
     * @param int $researchId
     * @return Response
     */
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

    /**
     * @param int $researchId
     * @return Response
     */
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
