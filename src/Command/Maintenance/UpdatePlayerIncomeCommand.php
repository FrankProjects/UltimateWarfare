<?php

namespace FrankProjects\UltimateWarfare\Command\Maintenance;

use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Repository\PlayerRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRepository;
use FrankProjects\UltimateWarfare\Util\IncomeCalculator;
use FrankProjects\UltimateWarfare\Util\UpkeepCalculator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpdatePlayerIncomeCommand extends Command
{
    protected static $defaultName = 'game:maintenance:update:income';
    private PlayerRepository $playerRepository;
    private WorldRepository $worldRepository;
    private IncomeCalculator $incomeCalculator;
    private UpkeepCalculator $upkeepCalculator;

    public function __construct(
        PlayerRepository $playerRepository,
        WorldRepository $worldRepository,
        IncomeCalculator $incomeCalculator,
        UpkeepCalculator $upkeepCalculator
    ) {
        $this->playerRepository = $playerRepository;
        $this->worldRepository = $worldRepository;
        $this->incomeCalculator = $incomeCalculator;
        $this->upkeepCalculator = $upkeepCalculator;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Update resource income of all players')
            ->setHelp('Fix inconsistencies by updating resource income of all players...')
            ->addOption(
                'commit',
                null,
                InputOption::VALUE_OPTIONAL,
                'Should I save the changes?',
                false
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln([
            'Updating income resources of all players',
            '============',
            '',
        ]);

        $commit = $input->getOption('commit');
        $commit = ($commit !== false);

        foreach ($this->worldRepository->findAll() as $world) {
            $output->writeln("Processing World: {$world->getName()}");

            foreach ($world->getPlayers() as $player) {
                $this->processPlayer($output, $player, $commit);
            }
        }

        if (!$commit) {
            $output->writeln('Use --commit to actually save the changes!');
        }

        $output->writeln('Done!');

        return 0;
    }

    private function processPlayer(OutputInterface $output, Player $player, bool $commit): void
    {
        $output->writeln("Processing Player: {$player->getName()}");

        $income = $this->incomeCalculator->calculateIncomeForPlayer($player);
        $upkeep = $this->upkeepCalculator->calculateUpkeepForPlayer($player);

        if (!$player->getIncome()->equals($income) || !$player->getUpkeep()->equals($upkeep)) {
            $output->writeln("Mismatch found: {$player->getName()}");
            $output->writeln(print_r($income));
            $output->writeln(print_r($player->getIncome()));
            $output->writeln(print_r($upkeep));
            $output->writeln(print_r($player->getUpkeep()));

            $player->setIncome($income);
            $player->setUpkeep($upkeep);

            if ($commit) {
                $this->playerRepository->save($player);
            }
        }
    }
}
