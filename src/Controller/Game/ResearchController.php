<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\Research;
use FrankProjects\UltimateWarfare\Entity\ResearchNeeds;
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
        $notResearched = $this->researchRepository->findNotResearchedByPlayer($player);

        $completedResearchIds = $this->getCompletedResearchIds($player);
        $availableResearch = $this->filterAvailableResearch($notResearched, $completedResearchIds);

        return $this->render(
            'game/research.html.twig',
            [
                'player' => $player,
                'ongoingResearch' => $ongoingResearch,
                'researchArray' => $availableResearch
            ]
        );
    }

    /**
     * Get IDs of all completed research for the player
     *
     * @return array<int>
     */
    private function getCompletedResearchIds(Player $player): array
    {
        $completedIds = [];

        foreach ($player->getPlayerResearch() as $researchPlayer) {
            if ($researchPlayer->getActive()) {
                $completedIds[] = $researchPlayer->getResearch()->getId();
            }
        }

        return $completedIds;
    }

    /**
     * Filter research to only include those with all prerequisites met
     *
     * @param array<Research> $notResearched
     * @param array<int> $completedResearchIds
     * @return array<Research>
     */
    private function filterAvailableResearch(array $notResearched, array $completedResearchIds): array
    {
        $availableResearch = [];

        foreach ($notResearched as $research) {
            $isAvailable = true;
            foreach ($research->getResearchNeeds() as $researchNeed) {
                $requiredResearchId = $researchNeed->getRequiredResearch()->getId();

                if (in_array($requiredResearchId, $completedResearchIds, true)) {
                    // Do nothing
                } else {
                    $isAvailable = false;
                }
            }

            // Only include research where all prerequisites are met
            if ($isAvailable === true) {
                $availableResearch[] = $research;
            }
        }

        return $availableResearch;
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
