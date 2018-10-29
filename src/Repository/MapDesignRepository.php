<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\MapDesign;

interface MapDesignRepository
{
    /**
     * @param int $id
     * @return MapDesign|null
     */
    public function find(int $id): ?MapDesign;

    /**
     * @return MapDesign[]
     */
    public function findAll(): array;

    /**
     * @param MapDesign $mapDesign
     */
    public function save(MapDesign $mapDesign): void;
}
