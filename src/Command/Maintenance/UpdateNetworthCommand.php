<?php

namespace FrankProjects\UltimateWarfare\Command\Maintenance;

use FrankProjects\UltimateWarfare\Repository\PlayerRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRepository;
use FrankProjects\UltimateWarfare\Util\NetworthCalculator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateNetworthCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'game:maintenance:update:networth';

    /**
     * @var PlayerRepository
     */
    private $playerRepository;

    /**
     * @var WorldRepository
     */
    private $worldRepository;

    /**
     * @var NetworthCalculator
     */
    private $networthCalculator;

    /**
     * UpdateNetworthCommand constructor.
     *
     * @param PlayerRepository $playerRepository
     * @param WorldRepository $worldRepository
     * @param NetworthCalculator $networthCalculator
     */
    public function __construct(
        PlayerRepository $playerRepository,
        WorldRepository $worldRepository,
        NetworthCalculator $networthCalculator
    ) {
        $this->playerRepository = $playerRepository;
        $this->worldRepository = $worldRepository;
        $this->networthCalculator = $networthCalculator;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Update networth of all players');
        $this->setHelp('Fix incosistencies by updating networth of all players...');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $output->writeln([
            'Updating networth of all players',
            '============',
            '',
        ]);

        foreach ($this->worldRepository->findByPublic(true) as $world) {
            foreach ($world->getPlayers() as $player) {
                $networth = $this->networthCalculator->calculateNetworthForPlayer($player);
                if ($player->getNetworth() !== $networth) {
                    $output->writeln("Mismatch found: {$player->getName()} {$player->getNetworth()} => {$networth}");
                    $player->setNetworth($networth);
                    $this->playerRepository->save($player);
                }
            }
        }

        $output->writeln('Done!');
    }
}
