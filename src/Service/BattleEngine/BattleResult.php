<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\BattleEngine;

final class BattleResult
{
    /**
     * @var BattlePhase[]
     */
    private $battlePhases;

    /**
     * BattleResult constructor.
     *
     * @param BattlePhase[] $battlePhases
     */
    public function __construct(array $battlePhases)
    {
        $this->battlePhases = $battlePhases;
    }

    /**
     * @return BattlePhase[]
     */
    public function getBattlePhases(): array
    {
        return $this->battlePhases;
    }

    /**
     * @return bool
     */
    public function hasWon(): bool
    {
        $battlePhases = $this->getBattlePhases();
        $battlePhase = end($battlePhases);
        if ($battlePhase->getDefensePower() === 0 && $battlePhase->getAttackPower() > 0) {
            return true;
        }

        return false;
    }
}
