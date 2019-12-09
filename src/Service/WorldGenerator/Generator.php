<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\WorldGenerator;

use FrankProjects\UltimateWarfare\Entity\World\MapConfiguration;

interface Generator
{
    /**
     * @param MapConfiguration $mapConfiguration
     * @return array
     */
    public function generate(MapConfiguration $mapConfiguration): array;
}
