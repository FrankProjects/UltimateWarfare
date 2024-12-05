<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\WorldGenerator;

use FrankProjects\UltimateWarfare\Entity\WorldRegion;
use GdImage;
use RuntimeException;

abstract class AbstractImageBuilder
{
    protected GdImage $image;
    protected const string COLOR_RED = 'red';
    protected const string COLOR_GREEN = 'green';
    protected const string COLOR_BLUE = 'blue';

    protected function createImageResource(int $sizeX, int $sizeY): void
    {
        $this->ensureGD();

        $image = imagecreatetruecolor(max(1, $sizeX), max(1, $sizeY));
        if ($image === false) {
            throw new RuntimeException("imagecreatetruecolor failed for size {$sizeX}/{$sizeY}");
        }

        $this->image = $image;
    }

    protected function getWorldRegionColor(WorldRegion $worldRegion): int
    {
        $color = imagecolorallocate(
            $this->image,
            $this->getTypeImageColorsForWorldRegionAndColor($worldRegion, self::COLOR_RED),
            $this->getTypeImageColorsForWorldRegionAndColor($worldRegion, self::COLOR_GREEN),
            $this->getTypeImageColorsForWorldRegionAndColor($worldRegion, self::COLOR_BLUE)
        );

        if ($color === false) {
            throw new RuntimeException("imagecolorallocate failed for WorldRegion {$worldRegion->getId()}");
        }

        return $color;
    }

    /**
     * @return int<0, 255>
     */
    private function getTypeImageColorsForWorldRegionAndColor(WorldRegion $worldRegion, string $color): int
    {
        $typeColor = $this->getTypeImageColors()[$worldRegion->getType()][$color];
        if ($typeColor > 255 || $typeColor < 0) {
            throw new RuntimeException("Int should be between 0 and 255");
        }

        return $typeColor;
    }

    /**
     * @return array<string, array<string, int>>
     */
    protected function getTypeImageColors(): array
    {
        return [
            WorldRegion::TYPE_WATER => [self::COLOR_RED => 173, self::COLOR_GREEN => 216, self::COLOR_BLUE => 230],
            WorldRegion::TYPE_BEACH => [self::COLOR_RED => 255, self::COLOR_GREEN => 255, self::COLOR_BLUE => 0],
            WorldRegion::TYPE_FORREST => [self::COLOR_RED => 0, self::COLOR_GREEN => 128, self::COLOR_BLUE => 0],
            WorldRegion::TYPE_MOUNTAIN => [self::COLOR_RED => 128, self::COLOR_GREEN => 128, self::COLOR_BLUE => 128]
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
