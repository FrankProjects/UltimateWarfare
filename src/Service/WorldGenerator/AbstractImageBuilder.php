<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\WorldGenerator;

use FrankProjects\UltimateWarfare\Entity\WorldRegion;
use RuntimeException;

abstract class AbstractImageBuilder
{
    /** @var resource|\GdImage */
    protected $image;

    protected function createImageResource(int $sizeX, int $sizeY): void
    {
        $this->ensureGD();

        $image = @imagecreatetruecolor($sizeX, $sizeY);
        if ($image === false) {
            throw new RunTimeException("imagecreatetruecolor failed for size {$sizeX}/{$sizeY}");
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
            throw new RunTimeException("imagecolorallocate failed for WorldRegion {$worldRegion->getId()}");
        }

        return $color;
    }

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
        if ($this->image === null) {
            throw new RunTimeException("Image is null  for {$imagePath}");
        }

        imagejpeg($this->image, $imagePath);
    }

    protected function ensureGD(): void
    {
        $testGD = get_extension_funcs("gd");
        if (!$testGD) {
            throw new RunTimeException("GD not installed!");
        }
    }
}
