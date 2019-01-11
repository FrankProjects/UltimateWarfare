<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\BattleEngine\BattlePhase;

use FrankProjects\UltimateWarfare\Service\BattleEngine\BattlePhase;

final class Sea extends BattlePhase
{
    /**
     * @return int
     */
    public function getAttackPower(): int
    {
        $power = 0;
        foreach ($this->getAttackerGameUnits() as $gameUnit) {
            $power += $gameUnit->getGameUnit()->getBattleStats()->getSeaAttack() * $gameUnit->getAmount();
        }

        return $power;
    }

    /**
     * @return int
     */
    public function getDefensePower(): int
    {
        $power = 0;
        foreach ($this->getAttackerGameUnits() as $gameUnit) {
            $power += $gameUnit->getGameUnit()->getBattleStats()->getSeaDefence() * $gameUnit->getAmount();
        }

        return $power;
    }
}
