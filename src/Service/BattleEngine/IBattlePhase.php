<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\BattleEngine;

interface IBattlePhase
{
    public function getAttackPower(): int;
    public function getDefensePower(): int;
}
