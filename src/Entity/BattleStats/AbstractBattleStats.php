<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity\BattleStats;

abstract class AbstractBattleStats
{
    protected int $attack = 0;
    protected int $attackSpeed = 0;
    protected int $defence = 0;
    protected int $defenceSpeed = 0;

    public function __construct(int $attack = 0, int $attackSpeed = 0, int $defence = 0, int $defenceSpeed = 0)
    {
        $this->attack = $attack;
        $this->attackSpeed = $attackSpeed;
        $this->defence = $defence;
        $this->defenceSpeed = $defenceSpeed;
    }

    public function getAttack(): int
    {
        return $this->attack;
    }

    public function getAttackSpeed(): int
    {
        return $this->attackSpeed;
    }

    public function getDefence(): int
    {
        return $this->defence;
    }

    public function getDefenceSpeed(): int
    {
        return $this->defenceSpeed;
    }
}
