<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Command\Maintenance;

use FrankProjects\UltimateWarfare\Entity\World;
use FrankProjects\UltimateWarfare\Entity\WorldSector;
use FrankProjects\UltimateWarfare\Repository\WorldRepository;
use FrankProjects\UltimateWarfare\Service\WorldImageGeneratorService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'game:maintenance:update:world-map-images')]
class RegenerateWordMapImagesCommand extends Command
{
    private WorldRepository $worldRepository;
    private WorldImageGeneratorService $worldImageGeneratorService;

    public function __construct(
        WorldRepository $worldRepository,
        WorldImageGeneratorService $worldImageGeneratorService
    ) {
        $this->worldRepository = $worldRepository;
        $this->worldImageGeneratorService = $worldImageGeneratorService;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Regenerate all world map images')
            ->setHelp('Make sure all images are generated based on latest map generator');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln(
            [
                'Regenerating all world map images',
                '============',
                '',
            ]
        );

        foreach ($this->worldRepository->findAll() as $world) {
            $this->processWorld($output, $world);
        }

        $output->writeln('Done!');

        return Command::SUCCESS;
    }

    private function processWorld(OutputInterface $output, World $world): void
    {
        $output->writeln("Processing World: {$world->getName()}");
        $output->write("\tGenerating World image");
        $this->worldImageGeneratorService->generateWorldImage($world);
        $output->writeln(' - <info>OK</info>');
        foreach ($world->getWorldSectors() as $worldSector) {
            $this->processWorldSector($output, $worldSector);
        }
    }

    private function processWorldSector(OutputInterface $output, WorldSector $worldSector): void
    {
        $output->write("\t\tGenerating WorldSector image: {$worldSector->getId()}");
        $this->worldImageGeneratorService->generateWorldSectorImage($worldSector);
        $output->writeln(' - <info>OK</info>');
    }
}
