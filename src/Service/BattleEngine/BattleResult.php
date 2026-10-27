<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\BattleEngine;

/**
 * @phpstan-import-type LostUnits from BattleLossCalculator
 */
final class BattleResult
{
    /**
     * @var BattlePhase[]
     */
    private array $battlePhases;

    /**
     * @var array<string>
     */
    private array $preludeLog;

    /**
     * @var LostUnits|null
     */
    private ?array $lostUnits = null;

    /**
     * BattleResult constructor.
     *
     * @param BattlePhase[] $battlePhases
     * @param array<string> $preludeLog What happened before the battle phases, like arriving reinforcements
     */
    public function __construct(array $battlePhases, array $preludeLog = [])
    {
        $this->battlePhases = $battlePhases;
        $this->preludeLog = $preludeLog;
    }

    /**
     * @param LostUnits $lostUnits
     */
    public function setLostUnits(array $lostUnits): void
    {
        $this->lostUnits = $lostUnits;
    }

    /**
     * @return LostUnits|null
     */
    public function getLostUnits(): ?array
    {
        return $this->lostUnits;
    }

    /**
     * @return BattlePhase[]
     */
    public function getBattlePhases(): array
    {
        return $this->battlePhases;
    }

    /**
     * @return array<string>
     */
    public function getBattleLog(): array
    {
        $battleLog = $this->preludeLog;
        foreach ($this->battlePhases as $battlePhase) {
            foreach ($battlePhase->getBattleLog() as $line) {
                $battleLog[] = $line;
            }
        }

        return $battleLog;
    }

    public function hasWon(): bool
    {
        $battlePhase = array_last($this->getBattlePhases());
        if ($battlePhase instanceof BattlePhase === false) {
            return false;
        }
        if ($battlePhase->getDefensePower() === 0 && $battlePhase->getAttackPower() > 0) {
            return true;
        }

        return false;
    }
}
