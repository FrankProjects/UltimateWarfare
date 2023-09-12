<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\WorldGenerator;

use FrankProjects\UltimateWarfare\Entity\World\MapConfiguration;

interface Generator
{
    /**
     * @return array<int, array<int, float>>
     */
    public function generate(MapConfiguration $mapConfiguration): array;
}
