<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\BattleEngine\BattlePhase;

use FrankProjects\UltimateWarfare\Service\BattleEngine\BattlePhase;

final class Air extends BattlePhase
{
    /**
     * @return int
     */
    public function getAttackPower(): int
    {
        $power = 0;
        foreach ($this->getAttackerGameUnits() as $gameUnit) {
            $power += $gameUnit->getGameUnit()->getBattleStats()->getAirAttack() * $gameUnit->getAmount();
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
            $power += $gameUnit->getGameUnit()->getBattleStats()->getAirDefence() * $gameUnit->getAmount();
        }

        return $power;
    }
}
