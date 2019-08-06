<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\WorldGenerator\ImageBuilder;

use FrankProjects\UltimateWarfare\Entity\World;
use FrankProjects\UltimateWarfare\Service\WorldGenerator\AbstractImageBuilder;

class WorldImageBuilder extends AbstractImageBuilder
{
    public function generateForWorld(World $world): void
    {
        $size = (int)sqrt(count($world->getWorldRegions()));
        $this->createImageResource($size, $size);
        foreach ($world->getWorldRegions() as $worldRegion) {
            imagesetpixel($this->image, $worldRegion->getX() - 1, $worldRegion->getY() - 1, $this->getWorldRegionColor($worldRegion));
        }
    }
}
