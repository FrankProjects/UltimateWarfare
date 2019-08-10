<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\WorldCountry;

interface WorldCountryRepository
{
    /**
     * @param int $id
     * @return WorldCountry|null
     */
    public function find(int $id): ?WorldCountry;

    /**
     * @param WorldCountry $worldCountry
     */
    public function save(WorldCountry $worldCountry): void;
}
