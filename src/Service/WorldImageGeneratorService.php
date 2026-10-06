<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service;

use FrankProjects\UltimateWarfare\Entity\World;
use FrankProjects\UltimateWarfare\Repository\WorldRepository;
use FrankProjects\UltimateWarfare\Service\WorldGenerator\ImageBuilder\WorldImageBuilder;

final class WorldImageGeneratorService
{
    private WorldRepository $worldRepository;

    public function __construct(
        WorldRepository $worldRepository
    ) {
        $this->worldRepository = $worldRepository;
    }

    public function generateWorldImage(World $world): void
    {
        // Refresh object from DB, otherwise world image generation will fail
        $this->worldRepository->refresh($world);

        $worldImageBuilder = new WorldImageBuilder();
        $imageData = $worldImageBuilder->generateForWorld($world);

        $world->setImageData($imageData);
        $this->worldRepository->save($world);
    }
}
