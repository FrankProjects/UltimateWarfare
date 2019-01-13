<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity\GameUnit\BattleStats;

abstract class AbstractBattleStats
{
    /**
     * @var int
     */
    protected $attack = 0;

    /**
     * @var int
     */
    protected $attackSpeed = 0;

    /**
     * @var int
     */
    protected $defence = 0;

    /**
     * @var int
     */
    protected $defenceSpeed = 0;

    /**
     * Set attack
     *
     * @param int $attack
     */
    public function setAttack(int $attack): void
    {
        $this->attack = $attack;
    }

    /**
     * Get attack
     *
     * @return int
     */
    public function getAttack(): int
    {
        return $this->attack;
    }

    /**
     * Set attackSpeed
     *
     * @param int $attackSpeed
     */
    public function setAttackSpeed(int $attackSpeed): void
    {
        $this->attackSpeed = $attackSpeed;
    }

    /**
     * Get attackSpeed
     *
     * @return int
     */
    public function getAttackSpeed(): int
    {
        return $this->attackSpeed;
    }

    /**
     * Set defence
     *
     * @param int $defence
     */
    public function setDefence(int $defence): void
    {
        $this->defence = $defence;
    }

    /**
     * Get defence
     *
     * @return int
     */
    public function getDefence(): int
    {
        return $this->defence;
    }

    /**
     * Set defenceSpeed
     *
     * @param int $defenceSpeed
     */
    public function setDefenceSpeed(int $defenceSpeed): void
    {
        $this->defenceSpeed = $defenceSpeed;
    }

    /**
     * Get defenceSpeed
     *
     * @return int
     */
    public function getDefenceSpeed(): int
    {
        return $this->defenceSpeed;
    }
}
