<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\WorldGenerator;

use FrankProjects\UltimateWarfare\Entity\WorldRegion;
use RuntimeException;

abstract class AbstractImageBuilder
{
    /**
     * @var resource
     */
    protected $image;

    protected function createImageResource(int $sizeX, int $sizeY): void
    {
        $this->image = @imagecreatetruecolor($sizeX, $sizeY);
        if ($this->image === false) {
            throw new RunTimeException('imagecreatetruecolor failed');
        }
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
            throw new RunTimeException('imagecolorallocate failed');
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

    public function getImage(): string
    {
        if ($this->image === null) {
            return '';
        }

        ob_start();
        imagejpeg($this->image);
        $image_data = ob_get_contents();
        ob_end_clean();

        return base64_encode($image_data);
    }
}
