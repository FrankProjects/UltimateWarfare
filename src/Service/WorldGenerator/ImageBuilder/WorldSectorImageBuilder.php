<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\WorldGenerator\ImageBuilder;

use FrankProjects\UltimateWarfare\Entity\WorldSector;
use FrankProjects\UltimateWarfare\Service\WorldGenerator\AbstractImageBuilder;

class WorldSectorImageBuilder extends AbstractImageBuilder
{
    public function generateForWorldSector(WorldSector $worldSector): void
    {
        $size = (int)sqrt(count($worldSector->getWorldRegions())) * 5;
        if ($size === 0) {
            return;
        }

        $this->createImageResource($size, $size);
        foreach ($worldSector->getWorldRegions() as $worldRegion) {
            $x = (($worldSector->getX() - 1) * 5 * 5) + 1;
            $y = (($worldSector->getY() - 1) * 5 * 5) + 1;

            $startX = ($worldRegion->getX() - $x) * 5;
            $startY = ($worldRegion->getY() - $y) * 5;
            //echo $worldRegion->getX() . ', ' . $worldRegion->getY() . ' - ' . $startX . ',' . $startY . '<br />';
            for($i = 0; $i < 5; $i++) {
                for($j = 0; $j < 5; $j++) {
                    imagesetpixel($this->image, $startX + $i, $startY + $j, $this->getWorldRegionColor($worldRegion));
                }
            }
        }
    }
}
