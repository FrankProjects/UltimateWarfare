<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\Research;
use FrankProjects\UltimateWarfare\Repository\ResearchPlayerRepository;
use FrankProjects\UltimateWarfare\Repository\ResearchRegistry;
use FrankProjects\UltimateWarfare\Service\Action\ResearchActionService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Throwable;

final class ResearchController extends BaseGameController
{
    private ResearchRegistry $researchRegistry;
    private ResearchPlayerRepository $researchPlayerRepository;
    private ResearchActionService $researchActionService;

    public function __construct(
        ResearchRegistry $researchRegistry,
        ResearchPlayerRepository $researchPlayerRepository,
        ResearchActionService $researchActionService
    ) {
        $this->researchRegistry = $researchRegistry;
        $this->researchPlayerRepository = $researchPlayerRepository;
        $this->researchActionService = $researchActionService;
    }

    public function researchTreeApi(): JsonResponse
    {
        $player = $this->getPlayer();
        $completedLevels = $this->researchPlayerRepository->getCompletedLevelsBySlug($player);
        $ongoingResearchPlayers = $this->researchPlayerRepository->findOngoingByPlayer($player);

        $ongoingMap = [];
        foreach ($ongoingResearchPlayers as $rp) {
            $ongoingMap[$rp->getResearchSlug()] = $rp;
        }

        $allResearch = $this->researchRegistry->findEnabled();
        $now = time();
        $researchData = [];

        foreach ($allResearch as $research) {
            $researchData[] = $this->buildResearchPayload($research, $completedLevels, $ongoingMap, $now);
        }

        return new JsonResponse([
            'success' => true,
            'research' => $researchData,
            'playerCash' => $player->getResources()->getCash(),
        ]);
    }

    public function performResearchApi(string $researchSlug): JsonResponse
    {
        try {
            $player = $this->getPlayer();
            $this->researchActionService->performResearch($researchSlug, $player);

            return new JsonResponse([
                'success' => true,
                'message' => 'Successfully started a new research project!',
                'newCash' => $player->getResources()->getCash(),
            ]);
        } catch (Throwable $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function performCancelApi(string $researchSlug): JsonResponse
    {
        try {
            $this->researchActionService->performCancel($researchSlug, $this->getPlayer());

            return new JsonResponse([
                'success' => true,
                'message' => 'Successfully cancelled your research project!',
            ]);
        } catch (Throwable $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @param array<string, int>                                     $completedLevels
     * @param array<string, \FrankProjects\UltimateWarfare\Entity\ResearchPlayer> $ongoingMap
     * @return array<string, mixed>
     */
    private function buildResearchPayload(
        Research $research,
        array $completedLevels,
        array $ongoingMap,
        int $now,
    ): array {
        $slug = $research->getSlug();
        $currentLevel = $completedLevels[$slug] ?? 0;
        $maxLevel = $research->getMaxLevel();
        $nextLevel = $currentLevel + 1;
        $isOngoing = isset($ongoingMap[$slug]);
        $isMaxed = $currentLevel >= $maxLevel;

        if ($isOngoing) {
            $status = 'researching';
        } elseif ($isMaxed) {
            $status = 'maxed';
        } else {
            $status = $this->arePrerequisitesMet($research, $nextLevel, $completedLevels)
                ? 'available'
                : 'locked';
        }

        $payload = [
            'slug' => $slug,
            'name' => $research->getName(),
            'description' => $research->getDescription(),
            'image' => $research->getImage(),
            'currentLevel' => $currentLevel,
            'maxLevel' => $maxLevel,
            'status' => $status,
            'nextCost' => $isMaxed ? null : $research->getCost($nextLevel),
            'nextDuration' => $isMaxed ? null : $research->getTimestamp($nextLevel),
            'prerequisites' => $isMaxed ? [] : $research->getPrerequisiteDescriptions($nextLevel),
            'targetLevel' => null,
            'completionTimestamp' => null,
            'remainingSeconds' => null,
        ];

        if ($isOngoing) {
            $rp = $ongoingMap[$slug];
            $payload['targetLevel'] = $rp->getLevel();
            $payload['completionTimestamp'] = $rp->getCompletionTimestamp();
            $payload['remainingSeconds'] = max(0, $rp->getCompletionTimestamp() - $now);
        }

        return $payload;
    }

    /**
     * @param array<string, int> $completedLevels
     */
    private function arePrerequisitesMet(Research $research, int $level, array $completedLevels): bool
    {
        foreach ($research->getPrerequisites($level) as $prereqClass => $minLevel) {
            $prereqSlug = (new $prereqClass())->getSlug();
            $playerLevel = $completedLevels[$prereqSlug] ?? 0;
            if ($playerLevel < $minLevel) {
                return false;
            }
        }

        return true;
    }
}
