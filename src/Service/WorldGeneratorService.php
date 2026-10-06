<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service;

use FrankProjects\UltimateWarfare\Entity\World;
use FrankProjects\UltimateWarfare\Entity\World\MapConfiguration;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;
use FrankProjects\UltimateWarfare\Repository\WorldRegionRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRepository;
use FrankProjects\UltimateWarfare\Service\WorldGenerator\PerlinNoiseGenerator;
use RuntimeException;

final class WorldGeneratorService
{
    private WorldRepository $worldRepository;
    private WorldRegionRepository $worldRegionRepository;
    private PerlinNoiseGenerator $worldGenerator;
    private WorldImageGeneratorService $worldImageGeneratorService;

    public function __construct(
        WorldRepository $worldRepository,
        WorldRegionRepository $worldRegionRepository,
        PerlinNoiseGenerator $worldGenerator,
        WorldImageGeneratorService $worldImageGeneratorService
    ) {
        $this->worldRepository = $worldRepository;
        $this->worldRegionRepository = $worldRegionRepository;
        $this->worldGenerator = $worldGenerator;
        $this->worldImageGeneratorService = $worldImageGeneratorService;
    }

    public function generateBasicWorld(): void
    {
        $world = new World();
        $this->worldRepository->save($world);
        $this->generate($world, true);

        $resources = $world->getResources();
        $resources->setCash(25000);
        $resources->setFood(1000);
        $resources->setSteel(200);
        $resources->setWood(500);

        $world->setName('Game World #' . $world->getId());
        $world->setDescription('Standard generated game world');
        $world->setPublic(true);
        $world->setStarttime(time());
        $world->setEndTimestamp(time() + 3600 * 24 * 365);
        $world->setMaxPlayers(100);
        $world->setFederationLimit(10);
        $world->setStatus(1);
        $world->setResources($resources);
        $this->worldRepository->save($world);
    }

    /**
     * @return array<int, array<int, float>>
     */
    public function generate(World $world, bool $save): array
    {
        $mapConfiguration = $world->getMapConfiguration();
        if ($mapConfiguration->getSeed() === 0) {
            $mapConfiguration->setSeed(intval(microtime(true)));
        }
        $map = $this->worldGenerator->generate($mapConfiguration);

        if ($save) {
            $this->generateWorldRegions($world, $map, $mapConfiguration);
            $this->worldImageGeneratorService->generateWorldImage($world);
        }

        return $map;
    }

    /**
     * @param array<int, array<int, float>> $map
     */
    private function generateWorldRegions(
        World $world,
        array $map,
        MapConfiguration $mapConfiguration
    ): void {
        if ($mapConfiguration->getSize() !== 25) {
            throw new RuntimeException("MapGenerator only supports size 25!");
        }

        foreach ($map as $x => $yData) {
            $x++;
            foreach ($yData as $y => $z) {
                $y++;
                $z = intval($z * 100);
                $type = $this->getTypeFromConfiguration($mapConfiguration, $z);
                $space = $this->getRandomSpaceFromType($type);

                $worldRegion = $this->worldRegionRepository->findByWorldXY($world, $x, $y);
                if ($worldRegion === null) {
                    $worldRegion = WorldRegion::createForWorld($world, $x, $y, $z, $type, $space);
                } else {
                    $worldRegion->setZ($z);
                    $worldRegion->setType($type);
                    $worldRegion->setSpace($space);
                    $worldRegion->setPopulation($space * 10);
                }
                $this->worldRegionRepository->save($worldRegion);
            }
        }
    }

    private function getTypeFromConfiguration(MapConfiguration $mapConfiguration, int $z): string
    {
        if ($z < $mapConfiguration->getDeepWaterLevel()) {
            return WorldRegion::TYPE_DEEP_WATER;
        } elseif ($z < $mapConfiguration->getWaterLevel()) {
            return WorldRegion::TYPE_WATER;
        } elseif ($z < $mapConfiguration->getShallowWaterLevel()) {
            return WorldRegion::TYPE_SHALLOW_WATER;
        } elseif ($z < $mapConfiguration->getSandLevel()) {
            return WorldRegion::TYPE_SAND;
        } elseif ($z < $mapConfiguration->getGrasslandLevel()) {
            return WorldRegion::TYPE_GRASSLAND;
        } elseif ($z < $mapConfiguration->getForestLevel()) {
            return WorldRegion::TYPE_FOREST;
        } elseif ($z < $mapConfiguration->getHillsLevel()) {
            return WorldRegion::TYPE_HILLS;
        }
        return WorldRegion::TYPE_MOUNTAIN;
    }

    private function getRandomSpaceFromType(string $type): int
    {
        return match ($type) {
            WorldRegion::TYPE_MOUNTAIN => rand(800, 1500),
            WorldRegion::TYPE_HILLS => rand(1200, 2000),
            WorldRegion::TYPE_FOREST => rand(1500, 2500),
            WorldRegion::TYPE_GRASSLAND => rand(2000, 3000),
            WorldRegion::TYPE_SAND => rand(500, 1000),
            default => 0,
        };
    }
}
