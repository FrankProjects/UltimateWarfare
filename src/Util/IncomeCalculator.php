<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Util;

use FrankProjects\UltimateWarfare\Entity\AbstractGameResources;
use FrankProjects\UltimateWarfare\Entity\Player;

final class IncomeCalculator extends AbstractPlayerCalculator
{
    public function calculateIncomeForPlayer(Player $player): AbstractGameResources
    {
        $this->abstractGameResources = new Player\Income();

        $this->calculateForFleets($player, AbstractPlayerCalculator::ABSTRACT_GAME_RESOURCES_INCOME);
        $this->calculateForWorldRegions($player, AbstractPlayerCalculator::ABSTRACT_GAME_RESOURCES_INCOME);

        return $this->abstractGameResources;
    }
}
