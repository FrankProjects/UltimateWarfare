<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\WorldGenerator;

use FrankProjects\UltimateWarfare\Entity\WorldRegion;
use GdImage;
use RuntimeException;

abstract class AbstractImageBuilder
{
    protected GdImage $image;

    protected function createImageResource(int $sizeX, int $sizeY): void
    {
        $this->ensureGD();

        $image = imagecreatetruecolor($sizeX, $sizeY);
        if ($image === false) {
            throw new RuntimeException("imagecreatetruecolor failed for size {$sizeX}/{$sizeY}");
        }

        $this->image = $image;
    }

    protected function getWorldRegionColor(WorldRegion $worldRegion): int
    {
        $color = imagecolorallocate(
            $this->image,
            $this->getTypeImageColors()[$worldRegion->getType()]['red'],
            $this->getTypeImageColors()[$worldRegion->getType()]['green'],
            $this->getTypeImageColors()[$worldRegion->getType()]['blue']
        );

        if ($color === false) {
            throw new RuntimeException("imagecolorallocate failed for WorldRegion {$worldRegion->getId()}");
        }

        return $color;
    }


    /**
     * @return array<string, array<string, int>>
     */
    protected function getTypeImageColors(): array
    {
        return [
            WorldRegion::TYPE_WATER => ['red' => 173, 'green' => 216, 'blue' => 230],
            WorldRegion::TYPE_BEACH => ['red' => 255, 'green' => 255, 'blue' => 0],
            WorldRegion::TYPE_FORREST => ['red' => 0, 'green' => 128, 'blue' => 0],
            WorldRegion::TYPE_MOUNTAIN => ['red' => 128, 'green' => 128, 'blue' => 128]
        ];
    }

    protected function saveImage(string $imagePath): void
    {
        imagejpeg($this->image, $imagePath);
    }

    protected function ensureGD(): void
    {
        if (extension_loaded('gd') === false) {
            throw new RuntimeException("GD not installed!");
        }
    }
}
