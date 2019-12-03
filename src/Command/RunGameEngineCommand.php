<?php

namespace FrankProjects\UltimateWarfare\Command;

use FrankProjects\UltimateWarfare\Service\GameEngine;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RunGameEngineCommand extends Command
{
    protected static $defaultName = 'game:engine:run';

    /**
     * @var GameEngine
     */
    private $gameEngine;

    /**
     * RunGameEngineCommand constructor.
     *
     * @param GameEngine $gameEngine
     */
    public function __construct(GameEngine $gameEngine)
    {
        $this->gameEngine = $gameEngine;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Run the GameEngine');
        $this->setHelp('Run the GameEngine to process all queued construction and research...');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // outputs multiple lines to the console (adding "\n" at the end of each line)
        $output->writeln([
            'Running the GameEngine',
            '============',
            '',
        ]);

        $this->gameEngine->run(null);
        $output->writeln('Done!');

        return 0;
    }
}
