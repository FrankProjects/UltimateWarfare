<?php

namespace FrankProjects\UltimateWarfare\Util;

use FrankProjects\UltimateWarfare\Entity\Player;

final class IncomeCalculator extends AbstractPlayerCalculator
{
    public function calculateIncomeForPlayer(Player $player): Player\Income
    {
        $this->abstractGameResources = new Player\Income();

        $this->calculateForFleets($player, AbstractPlayerCalculator::ABSTRACT_GAME_RESOURCES_INCOME);
        $this->calculateForWorldRegions($player, AbstractPlayerCalculator::ABSTRACT_GAME_RESOURCES_INCOME);

        return $this->abstractGameResources;
    }
}
