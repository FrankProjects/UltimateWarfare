<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity\GameUnit;

class BattleStats
{
    /**
     * @var int
     */
    private $health = 1;

    /**
     * @var int
     */
    private $armor = 1;

    /**
     * @var int
     */
    private $travelSpeed = 0;

    /**
     * @var int
     */
    private $airAttack = 0;

    /**
     * @var int
     */
    private $airAttackSpeed = 0;

    /**
     * @var int
     */
    private $airDefence = 0;

    /**
     * @var int
     */
    private $airDefenceSpeed = 0;

    /**
     * @var int
     */
    private $seaAttack = 0;

    /**
     * @var int
     */
    private $seaAttackSpeed = 0;

    /**
     * @var int
     */
    private $seaDefence = 0;

    /**
     * @var int
     */
    private $seaDefenceSpeed = 0;

    /**
     * @var int
     */
    private $groundAttack = 0;

    /**
     * @var int
     */
    private $groundAttackSpeed = 0;

    /**
     * @var int
     */
    private $groundDefence = 0;

    /**
     * @var int
     */
    private $groundDefenceSpeed = 0;

    /**
     * Set health
     *
     * @param int $health
     */
    public function setHealth(int $health): void
    {
        $this->health = $health;
    }

    /**
     * Get health
     *
     * @return int
     */
    public function getHealth(): int
    {
        return $this->health;
    }

    /**
     * Set armor
     *
     * @param int $armor
     */
    public function setArmor(int $armor): void
    {
        $this->armor = $armor;
    }

    /**
     * Get armor
     *
     * @return int
     */
    public function getArmor(): int
    {
        return $this->armor;
    }

    /**
     * Set travelSpeed
     *
     * @param int $travelSpeed
     */
    public function setTravelSpeed(int $travelSpeed): void
    {
        $this->travelSpeed = $travelSpeed;
    }

    /**
     * Get travelSpeed
     *
     * @return int
     */
    public function getTravelSpeed(): int
    {
        return $this->travelSpeed;
    }

    /**
     * Set airAttack
     *
     * @param int $airAttack
     */
    public function setAirAttack(int $airAttack): void
    {
        $this->airAttack = $airAttack;
    }

    /**
     * Get airAttack
     *
     * @return int
     */
    public function getAirAttack(): int
    {
        return $this->airAttack;
    }

    /**
     * Set airAttackSpeed
     *
     * @param int $airAttackSpeed
     */
    public function setAirAttackSpeed(int $airAttackSpeed): void
    {
        $this->airAttackSpeed = $airAttackSpeed;
    }

    /**
     * Get airAttackSpeed
     *
     * @return int
     */
    public function getAirAttackSpeed(): int
    {
        return $this->airAttackSpeed;
    }

    /**
     * Set airDefence
     *
     * @param int $airDefence
     */
    public function setAirDefence(int $airDefence): void
    {
        $this->airDefence = $airDefence;
    }

    /**
     * Get airDefence
     *
     * @return int
     */
    public function getAirDefence(): int
    {
        return $this->airDefence;
    }

    /**
     * Set airDefenceSpeed
     *
     * @param int $airDefenceSpeed
     */
    public function setAirDefenceSpeed(int $airDefenceSpeed): void
    {
        $this->airDefenceSpeed = $airDefenceSpeed;
    }

    /**
     * Get airDefenceSpeed
     *
     * @return int
     */
    public function getAirDefenceSpeed(): int
    {
        return $this->airDefenceSpeed;
    }

    /**
     * Set seaAttack
     *
     * @param int $seaAttack
     */
    public function setSeaAttack(int $seaAttack): void
    {
        $this->seaAttack = $seaAttack;
    }

    /**
     * Get seaAttack
     *
     * @return int
     */
    public function getSeaAttack(): int
    {
        return $this->seaAttack;
    }

    /**
     * Set seaAttackSpeed
     *
     * @param int $seaAttackSpeed
     */
    public function setSeaAttackSpeed(int $seaAttackSpeed): void
    {
        $this->seaAttackSpeed = $seaAttackSpeed;
    }

    /**
     * Get seaAttackSpeed
     *
     * @return int
     */
    public function getSeaAttackSpeed(): int
    {
        return $this->seaAttackSpeed;
    }

    /**
     * Set seaDefence
     *
     * @param int $seaDefence
     */
    public function setSeaDefence(int $seaDefence): void
    {
        $this->seaDefence = $seaDefence;
    }

    /**
     * Get seaDefence
     *
     * @return int
     */
    public function getSeaDefence(): int
    {
        return $this->seaDefence;
    }

    /**
     * Set seaDefenceSpeed
     *
     * @param int $seaDefenceSpeed
     */
    public function setSeaDefenceSpeed(int $seaDefenceSpeed): void
    {
        $this->seaDefenceSpeed = $seaDefenceSpeed;
    }

    /**
     * Get seaDefenceSpeed
     *
     * @return int
     */
    public function getSeaDefenceSpeed(): int
    {
        return $this->seaDefenceSpeed;
    }

    /**
     * Set groundAttack
     *
     * @param int $groundAttack
     */
    public function setGroundAttack(int $groundAttack): void
    {
        $this->groundAttack = $groundAttack;
    }

    /**
     * Get groundAttack
     *
     * @return int
     */
    public function getGroundAttack(): int
    {
        return $this->groundAttack;
    }

    /**
     * Set groundAttackSpeed
     *
     * @param int $groundAttackSpeed
     */
    public function setGroundAttackSpeed(int $groundAttackSpeed): void
    {
        $this->groundAttackSpeed = $groundAttackSpeed;
    }

    /**
     * Get groundAttackSpeed
     *
     * @return int
     */
    public function getGroundAttackSpeed(): int
    {
        return $this->groundAttackSpeed;
    }

    /**
     * Set groundDefence
     *
     * @param int $groundDefence
     */
    public function setGroundDefence(int $groundDefence): void
    {
        $this->groundDefence = $groundDefence;
    }

    /**
     * Get groundDefence
     *
     * @return int
     */
    public function getGroundDefence(): int
    {
        return $this->groundDefence;
    }

    /**
     * Set groundDefenceSpeed
     *
     * @param int $groundDefenceSpeed
     */
    public function setGroundDefenceSpeed(int $groundDefenceSpeed): void
    {
        $this->groundDefenceSpeed = $groundDefenceSpeed;
    }

    /**
     * Get groundDefenceSpeed
     *
     * @return int
     */
    public function getGroundDefenceSpeed(): int
    {
        return $this->groundDefenceSpeed;
    }
}
