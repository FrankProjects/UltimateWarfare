<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\Action;

use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\Research;
use FrankProjects\UltimateWarfare\Entity\ResearchPlayer;
use FrankProjects\UltimateWarfare\Repository\PlayerRepository;
use FrankProjects\UltimateWarfare\Repository\ResearchPlayerRepository;
use FrankProjects\UltimateWarfare\Repository\ResearchRegistry;
use RuntimeException;

final class ResearchActionService
{
    private ResearchRegistry $researchRegistry;
    private ResearchPlayerRepository $researchPlayerRepository;
    private PlayerRepository $playerRepository;

    public function __construct(
        ResearchRegistry $researchRegistry,
        ResearchPlayerRepository $researchPlayerRepository,
        PlayerRepository $playerRepository
    ) {
        $this->researchRegistry = $researchRegistry;
        $this->researchPlayerRepository = $researchPlayerRepository;
        $this->playerRepository = $playerRepository;
    }

    public function performResearch(string $researchSlug, Player $player): void
    {
        $research = $this->getResearchBySlug($researchSlug);
        $targetLevel = $this->resolveTargetLevel($research, $player);

        $researchPlayer = new ResearchPlayer();
        $researchPlayer->setPlayer($player);
        $researchPlayer->setResearchSlug($research->getSlug());
        $researchPlayer->setLevel($targetLevel);
        $researchPlayer->setTimestamp(time());
        $researchPlayer->setCompletionTimestamp(time() + $research->getTimestamp($targetLevel));

        $resources = $player->getResources();
        $resources->setCash($resources->getCash() - $research->getCost($targetLevel));

        $player->setResources($resources);
        $this->playerRepository->save($player);
        $this->researchPlayerRepository->save($researchPlayer);
    }

    public function performCancel(string $researchSlug, Player $player): void
    {
        $this->getResearchBySlug($researchSlug);

        /** @var ResearchPlayer $playerResearch */
        foreach ($player->getPlayerResearch() as $playerResearch) {
            if ($playerResearch->getResearchSlug() !== $researchSlug) {
                continue;
            }

            if ($playerResearch->getActive()) {
                continue;
            }

            $this->researchPlayerRepository->remove($playerResearch);
            return;
        }
    }

    private function getResearchBySlug(string $researchSlug): Research
    {
        $research = $this->researchRegistry->find($researchSlug);

        if ($research === null) {
            throw new RuntimeException('This technology does not exist!');
        }

        if (!$research->isEnabled()) {
            throw new RuntimeException('This technology is disabled!');
        }

        return $research;
    }

    private function resolveTargetLevel(Research $research, Player $player): int
    {
        $completedLevelsBySlug = [];
        $currentLevel = 0;

        /** @var ResearchPlayer $playerResearch */
        foreach ($player->getPlayerResearch() as $playerResearch) {
            if (!$playerResearch->getActive()) {
                throw new RuntimeException('You can only research 1 technology at a time!');
            }

            $slug = $playerResearch->getResearchSlug();
            $level = $playerResearch->getLevel();

            if (!isset($completedLevelsBySlug[$slug]) || $level > $completedLevelsBySlug[$slug]) {
                $completedLevelsBySlug[$slug] = $level;
            }

            if ($slug === $research->getSlug() && $level > $currentLevel) {
                $currentLevel = $level;
            }
        }

        $targetLevel = $currentLevel + 1;

        if ($targetLevel > $research->getMaxLevel()) {
            throw new RuntimeException('This technology is already at its maximum level!');
        }

        foreach ($research->getPrerequisites($targetLevel) as $prereqClass => $minLevel) {
            $prereqSlug = (new $prereqClass())->getSlug();
            $playerPrereqLevel = $completedLevelsBySlug[$prereqSlug] ?? 0;
            if ($playerPrereqLevel < $minLevel) {
                throw new RuntimeException('You do not have all required technologies!');
            }
        }

        if ($research->getCost($targetLevel) > $player->getResources()->getCash()) {
            throw new RuntimeException('You can not afford that!');
        }

        return $targetLevel;
    }
}
