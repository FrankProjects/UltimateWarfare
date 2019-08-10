<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service;

use FrankProjects\UltimateWarfare\Entity\World;
use FrankProjects\UltimateWarfare\Entity\WorldGeneratorConfiguration;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;
use FrankProjects\UltimateWarfare\Repository\WorldRegionRepository;
use FrankProjects\UltimateWarfare\Service\WorldGenerator\PerlinNoiseGenerator;

final class WorldGeneratorService
{
    /**
     * @var WorldRegionRepository
     */
    private $worldRegionRepository;

    /**
     * @var PerlinNoiseGenerator
     */
    private $worldGenerator;

    /**
     * WorldGeneratorService constructor
     *
     * @param WorldRegionRepository $worldRegionRepository
     * @param PerlinNoiseGenerator $worldGenerator
     */
    public function __construct(
        WorldRegionRepository $worldRegionRepository,
        PerlinNoiseGenerator $worldGenerator
    ) {
        $this->worldRegionRepository = $worldRegionRepository;
        $this->worldGenerator = $worldGenerator;
    }

    /**
     * @param World $world
     * @param WorldGeneratorConfiguration $worldGeneratorConfiguration
     * @param bool $save
     * @return array
     */
    public function generate(World $world, WorldGeneratorConfiguration $worldGeneratorConfiguration, bool $save): array
    {
        if ($worldGeneratorConfiguration->getSeed() === 0) {
            $worldGeneratorConfiguration->setSeed(intval(microtime(true)));
        }
        $map = $this->worldGenerator->generate($worldGeneratorConfiguration);

        if ($save) {
            $this->generateWorldRegions($world, $map, $worldGeneratorConfiguration);
        }

        return $map;
    }

    /**
     * @param World $world
     * @param array $map
     * @param WorldGeneratorConfiguration $worldGeneratorConfiguration
     */
    private function generateWorldRegions(World $world, array $map, WorldGeneratorConfiguration $worldGeneratorConfiguration): void
    {
        foreach ($map as $x => $yData) {
            $x++;
            foreach ($yData as $y => $z) {
                $y++;
                $z = intval($z * 100);
                $type = $this->getTypeFromConfiguration($worldGeneratorConfiguration, $z);
                $space = $this->getRandomSpaceFromType($type);
                $worldRegion = WorldRegion::createForWorld($world, $x, $y, $z, $type, $space);
                $this->worldRegionRepository->save($worldRegion);
            }
        }
    }

    /**
     * @param WorldGeneratorConfiguration $worldGeneratorConfiguration
     * @param int $z
     * @return string
     */
    private static function getTypeFromConfiguration(WorldGeneratorConfiguration $worldGeneratorConfiguration, int $z): string
    {
        if ($z < $worldGeneratorConfiguration->getWaterLevel()) {
            return WorldRegion::TYPE_WATER;
        } elseif ($z < $worldGeneratorConfiguration->getBeachLevel()) {
            return WorldRegion::TYPE_BEACH;
        } elseif ($z < $worldGeneratorConfiguration->getForrestLevel()) {
            return WorldRegion::TYPE_FORREST;
        }
        return WorldRegion::TYPE_MOUNTAIN;
    }
    /**
     * @param string $type
     * @return int
     */
    private static function getRandomSpaceFromType(string $type): int
    {
        if ($type === WorldRegion::TYPE_MOUNTAIN) {
            return rand(800, 1500);
        } elseif ($type === WorldRegion::TYPE_FORREST) {
            return rand(1500, 2500);
        }
        return 0;
    }
}
