<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Util;

use FrankProjects\UltimateWarfare\Entity\Player;

/**
 * @property Player\Income $abstractGameResources
 */
final class IncomeCalculator extends AbstractPlayerCalculator
{
    public function calculateIncomeForPlayer(Player $player): Player\Income
    {
        $this->abstractGameResources = new Player\Income();

        $this->calculateForFleets($player, AbstractPlayerCalculator::ABSTRACT_GAME_RESOURCES_INCOME);
        $this->calculateForWorldRegions($player, AbstractPlayerCalculator::ABSTRACT_GAME_RESOURCES_INCOME);

        $this->applyResearchMultipliers($player);

        return $this->abstractGameResources;
    }

    private function applyResearchMultipliers(Player $player): void
    {
        $oreLevel = $this->researchLevel($player, 'ore-extraction-improvements');
        $woodLevel = $this->researchLevel($player, 'advanced-wood-processing');

        if ($oreLevel > 0) {
            $multiplier = 1.0 + 0.10 * $oreLevel;
            $this->abstractGameResources->setSteel(
                (int) ($this->abstractGameResources->getSteel() * $multiplier)
            );
        }

        if ($woodLevel > 0) {
            $multiplier = 1.0 + 0.10 * $woodLevel;
            $this->abstractGameResources->setWood(
                (int) ($this->abstractGameResources->getWood() * $multiplier)
            );
        }
    }

    private function researchLevel(Player $player, string $researchSlug): int
    {
        $highest = 0;
        foreach ($player->getPlayerResearch() as $playerResearch) {
            if ($playerResearch->getActive() === false) {
                continue;
            }
            if ($playerResearch->getResearchSlug() !== $researchSlug) {
                continue;
            }
            if ($playerResearch->getLevel() > $highest) {
                $highest = $playerResearch->getLevel();
            }
        }

        return $highest;
    }
}
