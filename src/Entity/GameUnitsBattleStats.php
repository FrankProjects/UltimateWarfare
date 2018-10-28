<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

/**
 * GameUnitsBattleStats
 */
class GameUnitsBattleStats
{
    /**
     * @var int
     */
    private $unitId;

    /**
     * @var int
     */
    private $health;

    /**
     * @var int
     */
    private $armor;

    /**
     * @var int
     */
    private $travelSpeed;

    /**
     * @var int
     */
    private $airAttack;

    /**
     * @var int
     */
    private $airAttackSpeed;

    /**
     * @var int
     */
    private $airDefence;

    /**
     * @var int
     */
    private $airDefenceSpeed;

    /**
     * @var int
     */
    private $seaAttack;

    /**
     * @var int
     */
    private $seaAttackSpeed;

    /**
     * @var int
     */
    private $seaDefence;

    /**
     * @var int
     */
    private $seaDefenceSpeed;

    /**
     * @var int
     */
    private $groundAttack;

    /**
     * @var int
     */
    private $groundAttackSpeed;

    /**
     * @var int
     */
    private $groundDefence;

    /**
     * @var int
     */
    private $groundDefenceSpeed;


    /**
     * Get unitId
     *
     * @return int
     */
    public function getUnitId()
    {
        return $this->unitId;
    }

    /**
     * Set health
     *
     * @param int $health
     *
     * @return GameUnitsBattleStats
     */
    public function setHealth($health)
    {
        $this->health = $health;

        return $this;
    }

    /**
     * Get health
     *
     * @return int
     */
    public function getHealth()
    {
        return $this->health;
    }

    /**
     * Set armor
     *
     * @param int $armor
     *
     * @return GameUnitsBattleStats
     */
    public function setArmor($armor)
    {
        $this->armor = $armor;

        return $this;
    }

    /**
     * Get armor
     *
     * @return int
     */
    public function getArmor()
    {
        return $this->armor;
    }

    /**
     * Set travelSpeed
     *
     * @param int $travelSpeed
     *
     * @return GameUnitsBattleStats
     */
    public function setTravelSpeed($travelSpeed)
    {
        $this->travelSpeed = $travelSpeed;

        return $this;
    }

    /**
     * Get travelSpeed
     *
     * @return int
     */
    public function getTravelSpeed()
    {
        return $this->travelSpeed;
    }

    /**
     * Set airAttack
     *
     * @param int $airAttack
     *
     * @return GameUnitsBattleStats
     */
    public function setAirAttack($airAttack)
    {
        $this->airAttack = $airAttack;

        return $this;
    }

    /**
     * Get airAttack
     *
     * @return int
     */
    public function getAirAttack()
    {
        return $this->airAttack;
    }

    /**
     * Set airAttackSpeed
     *
     * @param int $airAttackSpeed
     *
     * @return GameUnitsBattleStats
     */
    public function setAirAttackSpeed($airAttackSpeed)
    {
        $this->airAttackSpeed = $airAttackSpeed;

        return $this;
    }

    /**
     * Get airAttackSpeed
     *
     * @return int
     */
    public function getAirAttackSpeed()
    {
        return $this->airAttackSpeed;
    }

    /**
     * Set airDefence
     *
     * @param int $airDefence
     *
     * @return GameUnitsBattleStats
     */
    public function setAirDefence($airDefence)
    {
        $this->airDefence = $airDefence;

        return $this;
    }

    /**
     * Get airDefence
     *
     * @return int
     */
    public function getAirDefence()
    {
        return $this->airDefence;
    }

    /**
     * Set airDefenceSpeed
     *
     * @param int $airDefenceSpeed
     *
     * @return GameUnitsBattleStats
     */
    public function setAirDefenceSpeed($airDefenceSpeed)
    {
        $this->airDefenceSpeed = $airDefenceSpeed;

        return $this;
    }

    /**
     * Get airDefenceSpeed
     *
     * @return int
     */
    public function getAirDefenceSpeed()
    {
        return $this->airDefenceSpeed;
    }

    /**
     * Set seaAttack
     *
     * @param int $seaAttack
     *
     * @return GameUnitsBattleStats
     */
    public function setSeaAttack($seaAttack)
    {
        $this->seaAttack = $seaAttack;

        return $this;
    }

    /**
     * Get seaAttack
     *
     * @return int
     */
    public function getSeaAttack()
    {
        return $this->seaAttack;
    }

    /**
     * Set seaAttackSpeed
     *
     * @param int $seaAttackSpeed
     *
     * @return GameUnitsBattleStats
     */
    public function setSeaAttackSpeed($seaAttackSpeed)
    {
        $this->seaAttackSpeed = $seaAttackSpeed;

        return $this;
    }

    /**
     * Get seaAttackSpeed
     *
     * @return int
     */
    public function getSeaAttackSpeed()
    {
        return $this->seaAttackSpeed;
    }

    /**
     * Set seaDefence
     *
     * @param int $seaDefence
     *
     * @return GameUnitsBattleStats
     */
    public function setSeaDefence($seaDefence)
    {
        $this->seaDefence = $seaDefence;

        return $this;
    }

    /**
     * Get seaDefence
     *
     * @return int
     */
    public function getSeaDefence()
    {
        return $this->seaDefence;
    }

    /**
     * Set seaDefenceSpeed
     *
     * @param int $seaDefenceSpeed
     *
     * @return GameUnitsBattleStats
     */
    public function setSeaDefenceSpeed($seaDefenceSpeed)
    {
        $this->seaDefenceSpeed = $seaDefenceSpeed;

        return $this;
    }

    /**
     * Get seaDefenceSpeed
     *
     * @return int
     */
    public function getSeaDefenceSpeed()
    {
        return $this->seaDefenceSpeed;
    }

    /**
     * Set groundAttack
     *
     * @param int $groundAttack
     *
     * @return GameUnitsBattleStats
     */
    public function setGroundAttack($groundAttack)
    {
        $this->groundAttack = $groundAttack;

        return $this;
    }

    /**
     * Get groundAttack
     *
     * @return int
     */
    public function getGroundAttack()
    {
        return $this->groundAttack;
    }

    /**
     * Set groundAttackSpeed
     *
     * @param int $groundAttackSpeed
     *
     * @return GameUnitsBattleStats
     */
    public function setGroundAttackSpeed($groundAttackSpeed)
    {
        $this->groundAttackSpeed = $groundAttackSpeed;

        return $this;
    }

    /**
     * Get groundAttackSpeed
     *
     * @return int
     */
    public function getGroundAttackSpeed()
    {
        return $this->groundAttackSpeed;
    }

    /**
     * Set groundDefence
     *
     * @param int $groundDefence
     *
     * @return GameUnitsBattleStats
     */
    public function setGroundDefence($groundDefence)
    {
        $this->groundDefence = $groundDefence;

        return $this;
    }

    /**
     * Get groundDefence
     *
     * @return int
     */
    public function getGroundDefence()
    {
        return $this->groundDefence;
    }

    /**
     * Set groundDefenceSpeed
     *
     * @param int $groundDefenceSpeed
     *
     * @return GameUnitsBattleStats
     */
    public function setGroundDefenceSpeed($groundDefenceSpeed)
    {
        $this->groundDefenceSpeed = $groundDefenceSpeed;

        return $this;
    }

    /**
     * Get groundDefenceSpeed
     *
     * @return int
     */
    public function getGroundDefenceSpeed()
    {
        return $this->groundDefenceSpeed;
    }
}
