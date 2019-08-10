<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service;

use FrankProjects\UltimateWarfare\Entity\World;
use FrankProjects\UltimateWarfare\Entity\WorldCountry;
use FrankProjects\UltimateWarfare\Entity\WorldGeneratorConfiguration;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;
use FrankProjects\UltimateWarfare\Entity\WorldSector;
use FrankProjects\UltimateWarfare\Repository\WorldCountryRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRegionRepository;
use FrankProjects\UltimateWarfare\Repository\WorldSectorRepository;
use FrankProjects\UltimateWarfare\Service\WorldGenerator\PerlinNoiseGenerator;

final class WorldGeneratorService
{
    /**
     * @var WorldCountryRepository
     */
    private $worldCountryRepository;

    /**
     * @var WorldRegionRepository
     */
    private $worldRegionRepository;

    /**
     * @var WorldSectorRepository
     */
    private $worldSectorRepository;

    /**
     * @var PerlinNoiseGenerator
     */
    private $worldGenerator;

    /**
     * WorldGeneratorService constructor
     *
     * @param WorldCountryRepository $worldCountryRepository
     * @param WorldRegionRepository $worldRegionRepository
     * @param WorldSectorRepository $worldSectorRepository
     * @param PerlinNoiseGenerator $worldGenerator
     */
    public function __construct(
        WorldCountryRepository $worldCountryRepository,
        WorldRegionRepository $worldRegionRepository,
        WorldSectorRepository $worldSectorRepository,
        PerlinNoiseGenerator $worldGenerator
    ) {
        $this->worldCountryRepository = $worldCountryRepository;
        $this->worldRegionRepository = $worldRegionRepository;
        $this->worldSectorRepository = $worldSectorRepository;
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
            $this->generateWorldSectors($world, $map, $worldGeneratorConfiguration);
        }

        return $map;
    }

    private function generateWorldSectors(World $world, array $map, WorldGeneratorConfiguration $worldGeneratorConfiguration): void
    {
        for ($y = 1; $y <= 5; $y++) {
            for ($x = 1; $x <= 5; $x++) {
                $worldSector = WorldSector::createForWorld($world, $x, $y);
                $this->worldSectorRepository->save($worldSector);

                $this->generateWorldCountries($worldSector, $map, $worldGeneratorConfiguration);
            }
        }
    }

    private function generateWorldCountries(WorldSector $worldSector, array $map, WorldGeneratorConfiguration $worldGeneratorConfiguration): void
    {
        for ($y = 1; $y <= 5; $y++) {
            for ($x = 1; $x <= 5; $x++) {
                $worldCountry = WorldCountry::createForWorldSector($worldSector, $x, $y);
                $this->worldCountryRepository->save($worldCountry);

                $this->generateWorldRegions($worldCountry, $map, $worldGeneratorConfiguration);
            }
        }
    }

    /**
     * @param WorldCountry $worldCountry
     * @param array $map
     * @param WorldGeneratorConfiguration $worldGeneratorConfiguration
     */
    private function generateWorldRegions(WorldCountry $worldCountry, array $map, WorldGeneratorConfiguration $worldGeneratorConfiguration): void
    {
        $worldSector = $worldCountry->getWorldSector();
        $startX = (($worldSector->getX() - 1) * 5 * 5) + (($worldCountry->getX() - 1) * 5) + 1;
        $startY = (($worldSector->getY() - 1) * 5 * 5) + (($worldCountry->getY() - 1) * 5) + 1;

        foreach ($map as $x => $yData) {
            $x++;
            foreach ($yData as $y => $z) {
                $y++;
                if ($x < $startX || $x >= $startX + 5 || $y < $startY || $y >= $startY + 5) {
                    continue;
                }
                $z = intval($z * 100);
                $type = $this->getTypeFromConfiguration($worldGeneratorConfiguration, $z);
                $space = $this->getRandomSpaceFromType($type);
                $worldRegion = WorldRegion::createForWorldCountry($worldCountry, $x, $y, $z, $type, $space);
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
