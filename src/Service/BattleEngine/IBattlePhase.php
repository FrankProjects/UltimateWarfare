<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\BattleEngine;

/**
 * Interface IBattlePhase
 */
interface IBattlePhase
{
    /**
     * @return int
     */
    public function getAttackPower(): int;

    /**
     * @return int
     */
    public function getDefensePower(): int;
}
