<?php

namespace FrankProjects\UltimateWarfare\Command\Maintenance;

use FrankProjects\UltimateWarfare\Entity\World;
use FrankProjects\UltimateWarfare\Entity\WorldSector;
use FrankProjects\UltimateWarfare\Repository\WorldRepository;
use FrankProjects\UltimateWarfare\Service\WorldImageGeneratorService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RegenerateWordMapImagesCommand extends Command
{
    protected static $defaultName = 'game:maintenance:update:world-map-images';

    /**
     * @var WorldRepository
     */
    private $worldRepository;

    /**
     * @var WorldImageGeneratorService
     */
    private $worldImageGeneratorService;

    /**
     * RegenerateWordMapImagesCommand constructor.
     *
     * @param WorldRepository $worldRepository
     * @param WorldImageGeneratorService $worldImageGeneratorService
     */
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

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln([
            'Regenerating all world map images',
            '============',
            '',
        ]);

        foreach ($this->worldRepository->findAll() as $world) {
            $this->processWorld($output, $world);
        }

        $output->writeln('Done!');

        return 0;
    }

    /**
     * @param OutputInterface $output
     * @param World $world
     */
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

    /**
     * @param OutputInterface $output
     * @param WorldSector $worldSector
     */
    private function processWorldSector(OutputInterface $output, WorldSector $worldSector): void
    {
        $output->write("\t\tGenerating WorldSector image: {$worldSector->getId()}");
        $this->worldImageGeneratorService->generateWorldSectorImage($worldSector);
        $output->writeln(' - <info>OK</info>');
    }
}