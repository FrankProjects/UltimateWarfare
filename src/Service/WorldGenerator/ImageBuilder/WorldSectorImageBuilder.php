<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\WorldGenerator\ImageBuilder;

use FrankProjects\UltimateWarfare\Entity\WorldSector;
use FrankProjects\UltimateWarfare\Service\WorldGenerator\AbstractImageBuilder;
use RuntimeException;

class WorldSectorImageBuilder extends AbstractImageBuilder
{
    public function generateForWorldSector(WorldSector $worldSector, string $path): void
    {
        $worldSectorRegionsCount = count($worldSector->getWorldRegions());
        $size = (int)sqrt($worldSectorRegionsCount) * 25;
        if ($size === 0) {
            throw new RuntimeException("Not enough WorldRegions[count={$worldSectorRegionsCount}] for WorldSector[id={$worldSector->getId()}]");
        }

        $this->createImageResource($size, $size);
        foreach ($worldSector->getWorldRegions() as $worldRegion) {
            $x = (($worldSector->getX() - 1) * 5) + 1;
            $y = (($worldSector->getY() - 1) * 5) + 1;

            $startX = ($worldRegion->getX() - $x) * 25;
            $startY = ($worldRegion->getY() - $y) * 25;
            for ($i = 0; $i < 25; $i++) {
                for ($j = 0; $j < 25; $j++) {
                    imagesetpixel($this->image, $startX + $i, $startY + $j, $this->getWorldRegionColor($worldRegion));
                }
            }
        }

        $this->saveImage($path);
    }
}
