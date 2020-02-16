<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service;

use FrankProjects\UltimateWarfare\Entity\World;
use FrankProjects\UltimateWarfare\Entity\WorldSector;
use FrankProjects\UltimateWarfare\Repository\WorldRepository;
use FrankProjects\UltimateWarfare\Repository\WorldSectorRepository;
use FrankProjects\UltimateWarfare\Service\WorldGenerator\ImageBuilder\WorldImageBuilder;
use FrankProjects\UltimateWarfare\Service\WorldGenerator\ImageBuilder\WorldSectorImageBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

final class WorldImageGeneratorService
{
    private ParameterBagInterface $params;
    private WorldRepository $worldRepository;
    private WorldSectorRepository $worldSectorRepository;

    public function __construct(
        ParameterBagInterface $params,
        WorldRepository $worldRepository,
        WorldSectorRepository $worldSectorRepository
    ) {
        $this->params = $params;
        $this->worldRepository = $worldRepository;
        $this->worldSectorRepository = $worldSectorRepository;
    }

    public function generateWorldImage(World $world): void
    {
        $worldImageName = $world->getId() . '.jpg';
        $worldImagePath = $this->params->get('kernel.project_dir') . '/public/images/world/' . $worldImageName;

        // Refresh object from DB, otherwise world image generation will fail
        $this->worldRepository->refresh($world);

        $worldImageBuilder = new WorldImageBuilder();
        $worldImageBuilder->generateForWorld($world, $worldImagePath);

        $world->setImage($worldImageName);
        $this->worldRepository->save($world);
    }

    public function generateWorldSectorImage(WorldSector $worldSector): void
    {
        $worldSectorImageName = $worldSector->getId() . '.jpg';
        $worldSectorImageDirectory = $this->params->get('kernel.project_dir') . '/public/images/world/sector/' . $worldSectorImageName;

        // Refresh object from DB, otherwise world image generation will fail
        $this->worldSectorRepository->refresh($worldSector);

        $worldSectorImageBuilder = new WorldSectorImageBuilder();
        $worldSectorImageBuilder->generateForWorldSector($worldSector, $worldSectorImageDirectory);

        $worldSector->setImage($worldSectorImageName);
        $this->worldSectorRepository->save($worldSector);
    }
}
