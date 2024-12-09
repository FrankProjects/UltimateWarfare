<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Command\Maintenance;

use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\World;
use FrankProjects\UltimateWarfare\Repository\PlayerRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRepository;
use FrankProjects\UltimateWarfare\Util\NetWorthCalculator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'game:maintenance:update:netWorth')]
class UpdateNetWorthCommand extends Command
{
    private PlayerRepository $playerRepository;
    private WorldRepository $worldRepository;
    private NetWorthCalculator $netWorthCalculator;

    public function __construct(
        PlayerRepository $playerRepository,
        WorldRepository $worldRepository,
        NetWorthCalculator $netWorthCalculator
    ) {
        $this->playerRepository = $playerRepository;
        $this->worldRepository = $worldRepository;
        $this->netWorthCalculator = $netWorthCalculator;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Update netWorth of all players')
            ->setHelp('Fix inconsistencies by updating netWorth of all players...')
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
        $output->writeln(
            [
                'Updating netWorth of all players',
                '============',
                '',
            ]
        );

        $commit = $input->getOption('commit');
        $commit = ($commit !== false);

        foreach ($this->worldRepository->findAll() as $world) {
            $this->processWorld($output, $world, $commit);
        }

        if (!$commit) {
            $output->writeln('Use --commit to actually save the changes!');
        }

        $output->writeln('Done!');

        return Command::SUCCESS;
    }

    private function processWorld(OutputInterface $output, World $world, bool $commit): void
    {
        $output->writeln("Processing World: {$world->getName()}");

        foreach ($world->getPlayers() as $player) {
            $this->processPlayer($output, $player, $commit);
        }
    }

    private function processPlayer(OutputInterface $output, Player $player, bool $commit): void
    {
        $output->writeln("Processing Player: {$player->getName()}");

        $netWorth = $this->netWorthCalculator->calculateNetWorthForPlayer($player);
        if ($player->getNetWorth() !== $netWorth) {
            $output->writeln("Mismatch found: {$player->getName()} {$player->getNetWorth()} => {$netWorth}");
            $player->setNetWorth($netWorth);

            if ($commit) {
                $this->playerRepository->save($player);
            }
        }
    }
}
