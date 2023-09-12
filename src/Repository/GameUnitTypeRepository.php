<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\GameUnitType;
use FrankProjects\UltimateWarfare\Exception\GameUnitTypeNotFoundException;

interface GameUnitTypeRepository
{

    /**
     * @throws GameUnitTypeNotFoundException
     */
    public function find(int $id): GameUnitType;

    /**
     * @return GameUnitType[]
     */
    public function findAll(): array;

    public function save(GameUnitType $gameUnitType): void;
}
