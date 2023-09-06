<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Util;

use FrankProjects\UltimateWarfare\Entity\Player;

/**
 * @property Player\Upkeep $abstractGameResources
 */
final class UpkeepCalculator extends AbstractPlayerCalculator
{
    public function calculateUpkeepForPlayer(Player $player): Player\Upkeep
    {
        $this->abstractGameResources = new Player\Upkeep();

        $this->calculateForFleets($player, AbstractPlayerCalculator::ABSTRACT_GAME_RESOURCES_UPKEEP);
        $this->calculateForWorldRegions($player, AbstractPlayerCalculator::ABSTRACT_GAME_RESOURCES_UPKEEP);

        return $this->abstractGameResources;
    }
}
