<?php

namespace FrankProjects\UltimateWarfare\Command\World;

use FrankProjects\UltimateWarfare\Entity\World;
use FrankProjects\UltimateWarfare\Form\Admin\WorldType;
use FrankProjects\UltimateWarfare\Repository\WorldRepository;
use FrankProjects\UltimateWarfare\Service\WorldGeneratorService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListWorldCommand extends Command
{
    protected static $defaultName = 'game:world:list';
    private WorldRepository $worldRepository;

    public function __construct(
        WorldRepository $worldRepository
    ) {
        $this->worldRepository = $worldRepository;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('List worlds')
            ->setHelp('List all game worlds');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln([
            'World list',
            '============',
            '',
        ]);

        $output->writeln("ID\tName\t\tStatus\tPlayers");

        foreach ($this->worldRepository->findAll() as $world) {
            $output->writeln($world->getId() . "\t" . $world->getName() . "\t" . $world->getStatus() . "\t" . count($world->getPlayers()) . '/' . $world->getMaxPlayers());
        }

        return 0;
    }
}
