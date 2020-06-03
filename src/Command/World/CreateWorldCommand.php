<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Command\World;

use FrankProjects\UltimateWarfare\Service\WorldGeneratorService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateWorldCommand extends Command
{
    protected static $defaultName = 'game:world:create';
    private WorldGeneratorService $worldGeneratorService;

    public function __construct(
        WorldGeneratorService $worldGeneratorService
    ) {
        $this->worldGeneratorService = $worldGeneratorService;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Create world')
            ->setHelp('Create a new game world with basic settings');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln([
            'Generating new world',
            '============',
            '',
        ]);

        $this->worldGeneratorService->generateBasicWorld();
        $output->writeln('Generated new map!');

        return 0;
    }
}
