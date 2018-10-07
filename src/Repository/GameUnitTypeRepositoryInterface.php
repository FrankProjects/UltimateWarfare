<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\GameUnitType;

interface GameUnitTypeRepositoryInterface
{
    /**
     * @param int $id
     * @return GameUnitType|null
     */
    public function find(int $id): ?GameUnitType;

    /**
     * @return array
     */
    public function findAll(): array;


    /**
     * @param GameUnitType $gameUnitType
     */
    public function save(GameUnitType $gameUnitType): void;
}
