<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\WorldGenerator;

use FrankProjects\UltimateWarfare\Entity\World\MapConfiguration;

class PerlinNoiseGenerator implements Generator
{
    private array $world;

    public function generate(MapConfiguration $mapConfiguration): array
    {
        $this->initWorld($mapConfiguration);

        for ($k = 0; $k < $this->getOctaves($mapConfiguration->getSize()); $k++) {
            $this->octave($mapConfiguration->getSize(), $mapConfiguration->getPersistence(), $k);
        }

        return $this->world;
    }

    private function octave(int $size, float $persistence, int $octave): void
    {
        $freq = pow(2, $octave);
        $amp = pow($persistence, $octave);

        $n = $m = $freq + 1;

        $array = [];
        for ($j = 0; $j < $m; $j++) {
            for ($i = 0; $i < $n; $i++) {
                $array[$j][$i] = $this->random() * $amp;
            }
        }

        $nx = $size / ($n - 1);
        $ny = $size / ($m - 1);

        for ($ky = 0; $ky < $size; $ky++) {
            for ($kx = 0; $kx < $size; $kx++) {
                $i = (int)($kx / $nx);
                $j = (int)($ky / $ny);

                $dx0 = $kx - $i * $nx;
                $dx1 = $nx - $dx0;
                $dy0 = $ky - $j * $ny;
                $dy1 = $ny - $dy0;

                $z = ($array[$j][$i] * $dx1 * $dy1
                        + $array[$j][$i + 1] * $dx0 * $dy1
                        + $array[$j + 1][$i] * $dx1 * $dy0
                        + $array[$j + 1][$i + 1] * $dx0 * $dy0)
                    / ($nx * $ny);

                $this->world[$ky][$kx] += $z;
            }
        }
    }

    private function initWorld(MapConfiguration $mapConfiguration): void
    {
        mt_srand(intval($mapConfiguration->getSeed() * $mapConfiguration->getPersistence() * $mapConfiguration->getSize()));

        $this->world = [];
        for ($y = 0; $y < $mapConfiguration->getSize(); $y++) {
            $this->world[$y] = [];
            for ($x = 0; $x < $mapConfiguration->getSize(); $x++) {
                $this->world[$y][$x] = 0;
            }
        }
    }

    private function random(): float
    {
        return mt_rand() / getrandmax();
    }

    private function getOctaves(int $size): int
    {
        return (int)log($size, 2);
    }
}
