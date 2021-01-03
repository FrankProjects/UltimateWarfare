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

    private function generateWorldRegions(World $world, array $map, MapConfiguration $mapConfiguration): void
    {
        foreach ($map as $x => $yData) {
            $x++;
            foreach ($yData as $y => $z) {
                $y++;
                $z = intval($z * 100);
                $type = $this->getTypeFromConfiguration($mapConfiguration, $z);
                $space = $this->getRandomSpaceFromType($type);
                $worldRegion = WorldRegion::createForWorld($world, $x, $y, $z, $type, $space);
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
