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
            WorldRegion::TYPE_DEEP_WATER => [self::COLOR_RED => 20, self::COLOR_GREEN => 50, self::COLOR_BLUE => 120],
            WorldRegion::TYPE_WATER => [self::COLOR_RED => 35, self::COLOR_GREEN => 80, self::COLOR_BLUE => 165],
            WorldRegion::TYPE_SHALLOW_WATER => [
                self::COLOR_RED => 70, self::COLOR_GREEN => 150, self::COLOR_BLUE => 195,
            ],
            WorldRegion::TYPE_SAND => [self::COLOR_RED => 210, self::COLOR_GREEN => 190, self::COLOR_BLUE => 140],
            WorldRegion::TYPE_GRASSLAND => [self::COLOR_RED => 75, self::COLOR_GREEN => 140, self::COLOR_BLUE => 60],
            WorldRegion::TYPE_FOREST => [self::COLOR_RED => 35, self::COLOR_GREEN => 85, self::COLOR_BLUE => 35],
            WorldRegion::TYPE_HILLS => [self::COLOR_RED => 110, self::COLOR_GREEN => 145, self::COLOR_BLUE => 75],
            WorldRegion::TYPE_MOUNTAIN => [self::COLOR_RED => 128, self::COLOR_GREEN => 128, self::COLOR_BLUE => 128],
        ];
    }

    protected function getImageBinary(): string
    {
        ob_start();
        imagejpeg($this->image);
        $data = ob_get_clean();
        if ($data === false) {
            throw new RuntimeException("Failed to capture image data");
        }

        return $data;
    }

    protected function ensureGD(): void
    {
        if (extension_loaded('gd') === false) {
            throw new RuntimeException("GD not installed!");
        }
    }
}
