<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\WorldGenerator\ImageBuilder;

use FrankProjects\UltimateWarfare\Entity\WorldCountry;
use FrankProjects\UltimateWarfare\Service\WorldGenerator\AbstractImageBuilder;

class WorldCountryImageBuilder extends AbstractImageBuilder
{
    public function generateForWorldCountry(WorldCountry $worldCountry): void
    {
        $size = (int)sqrt(count($worldCountry->getWorldRegions())) * 25;
        if ($size === 0) {
            return;
        }

        $worldSector = $worldCountry->getWorldSector();
        $this->createImageResource($size, $size);
        foreach ($worldCountry->getWorldRegions() as $worldRegion) {
            $x = (($worldSector->getX() - 1) * 5 * 5) + (($worldCountry->getX() - 1) * 5) + 1;
            $y = (($worldSector->getY() - 1) * 5 * 5) + (($worldCountry->getY() - 1) * 5) + 1;

            $startX = ($worldRegion->getX() - $x) * 25;
            $startY = ($worldRegion->getY() - $y) * 25;
            for ($i = 0; $i < 25; $i++) {
                for ($j = 0; $j < 25; $j++) {
                    imagesetpixel($this->image, $startX + $i, $startY + $j, $this->getWorldRegionColor($worldRegion));
                }
            }
        }
    }
}
