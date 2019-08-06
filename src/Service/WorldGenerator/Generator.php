<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\WorldGenerator;

use FrankProjects\UltimateWarfare\Entity\WorldGeneratorConfiguration;

interface Generator
{
    /**
     * @param WorldGeneratorConfiguration $worldGeneratorConfiguration
     * @return array
     */
    public function generate(WorldGeneratorConfiguration $worldGeneratorConfiguration): array;
}
