<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service;

use FrankProjects\UltimateWarfare\Entity\World;
use FrankProjects\UltimateWarfare\Entity\World\MapConfiguration;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;
use FrankProjects\UltimateWarfare\Entity\WorldSector;
use FrankProjects\UltimateWarfare\Repository\WorldRegionRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRepository;
use FrankProjects\UltimateWarfare\Repository\WorldSectorRepository;
use FrankProjects\UltimateWarfare\Service\WorldGenerator\PerlinNoiseGenerator;
use RuntimeException;

final class WorldGeneratorService
{
    private WorldRepository $worldRepository;
    private WorldRegionRepository $worldRegionRepository;
    private WorldSectorRepository $worldSectorRepository;
    private PerlinNoiseGenerator $worldGenerator;
    private WorldImageGeneratorService $worldImageGeneratorService;

    public function __construct(
        WorldRepository $worldRepository,
        WorldRegionRepository $worldRegionRepository,
        WorldSectorRepository $worldSectorRepository,
        PerlinNoiseGenerator $worldGenerator,
        WorldImageGeneratorService $worldImageGeneratorService
    ) {
        $this->worldRepository = $worldRepository;
        $this->worldRegionRepository = $worldRegionRepository;
        $this->worldSectorRepository = $worldSectorRepository;
        $this->worldGenerator = $worldGenerator;
        $this->worldImageGeneratorService = $worldImageGeneratorService;
    }

    public function generateBasicWorld(): void
    {
        $world = new World();
        $this->worldRepository->save($world);
        $this->generate($world, true, 0);

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

    public function generate(World $world, bool $save, int $sector): array
    {
        $mapConfiguration = $world->getMapConfiguration();
        if ($mapConfiguration->getSeed() === 0) {
            $mapConfiguration->setSeed(intval(microtime(true)));
        }
        $map = $this->worldGenerator->generate($mapConfiguration);

        if ($save) {
            $this->generateWorldSectors($world, $map, $mapConfiguration, $sector);
            $this->worldImageGeneratorService->generateWorldImage($world);
        }

        return $map;
    }

    private function generateWorldSectors(
        World $world,
        array $map,
        MapConfiguration $mapConfiguration,
        int $sector
    ): void {
        if ($mapConfiguration->getSize() != 25) {
            throw new RuntimeException("MapGenerator only supports size 25!");
        }

        if ($sector !== 0) {
            // XXX TODO: Improve world generator speed by generating per sector! Allows us to build bigger maps
            return;
        }

        for ($y = 1; $y <= 5; $y++) {
            for ($x = 1; $x <= 5; $x++) {
                $worldSector = $this->worldSectorRepository->findByWorldXY($world, $x, $y);
                if ($worldSector === null) {
                    $worldSector = WorldSector::createForWorld($world, $x, $y);
                    $this->worldSectorRepository->save($worldSector);
                }
                $this->generateWorldRegions($worldSector, $map, $mapConfiguration);
                $this->worldImageGeneratorService->generateWorldSectorImage($worldSector);
            }
        }
    }

    private function generateWorldRegions(
        WorldSector $worldSector,
        array $map,
        MapConfiguration $mapConfiguration
    ): void {
        $startX = (($worldSector->getX() - 1) * 5) + 1;
        $startY = (($worldSector->getY() - 1) * 5) + 1;

        foreach ($map as $x => $yData) {
            $x++;
            foreach ($yData as $y => $z) {
                $y++;
                if ($x < $startX || $x >= $startX + 5 || $y < $startY || $y >= $startY + 5) {
                    continue;
                }
                $z = intval($z * 100);
                $type = $this->getTypeFromConfiguration($mapConfiguration, $z);
                $space = $this->getRandomSpaceFromType($type);

                $worldRegion = $this->worldRegionRepository->findByWorldXY($worldSector->getWorld(), $x, $y);
                if ($worldRegion === null) {
                    $worldRegion = WorldRegion::createForWorldSector($worldSector, $x, $y, $z, $type, $space);
                } else {
                    $worldRegion->setZ($z);
                    $worldRegion->setType($type);
                    $worldRegion->setSpace($space);
                    $worldRegion->setPopulation($space * 10);
                    $worldRegion->setWorldSector($worldSector);
                }
                $this->worldRegionRepository->save($worldRegion);
            }
        }
    }

    private function getTypeFromConfiguration(MapConfiguration $mapConfiguration, int $z): string
    {
        if ($z < $mapConfiguration->getWaterLevel()) {
            return WorldRegion::TYPE_WATER;
        } elseif ($z < $mapConfiguration->getBeachLevel()) {
            return WorldRegion::TYPE_BEACH;
        } elseif ($z < $mapConfiguration->getForrestLevel()) {
            return WorldRegion::TYPE_FORREST;
        }
        return WorldRegion::TYPE_MOUNTAIN;
    }

    private function getRandomSpaceFromType(string $type): int
    {
        if ($type === WorldRegion::TYPE_MOUNTAIN) {
            return rand(800, 1500);
        } elseif ($type === WorldRegion::TYPE_FORREST) {
            return rand(1500, 2500);
        }
        return 0;
    }
}
