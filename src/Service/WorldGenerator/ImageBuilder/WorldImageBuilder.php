<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\WorldGenerator\ImageBuilder;

use FrankProjects\UltimateWarfare\Entity\World;
use FrankProjects\UltimateWarfare\Service\WorldGenerator\AbstractImageBuilder;
use RuntimeException;

class WorldImageBuilder extends AbstractImageBuilder
{
    public function generateForWorld(World $world, string $path): void
    {
        $size = (int)sqrt(count($world->getWorldRegions())) * 5;
        if ($size === 0) {
            throw new RuntimeException("Not enough WorldRegions");
        }

        $this->createImageResource($size, $size);
        foreach ($world->getWorldRegions() as $worldRegion) {
            $startX = ($worldRegion->getX() - 1) * 5;
            $startY = ($worldRegion->getY() - 1) * 5;
            for ($i = 0; $i < 5; $i++) {
                for ($j = 0; $j < 5; $j++) {
                    imagesetpixel($this->image, $startX + $i, $startY + $j, $this->getWorldRegionColor($worldRegion));
                }
            }
        }

        $this->saveImage($path);
    }
}
