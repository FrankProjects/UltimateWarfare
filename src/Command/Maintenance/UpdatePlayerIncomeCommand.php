<?php

namespace FrankProjects\UltimateWarfare\Command\Maintenance;

use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\WorldRegionUnit;
use FrankProjects\UltimateWarfare\Repository\PlayerRepository;
use FrankProjects\UltimateWarfare\Repository\WorldRepository;
use FrankProjects\UltimateWarfare\Util\NetworthCalculator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdatePlayerIncomeCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'game:maintenance:update:income';

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
     * UpdatePlayerIncomeCommand constructor.
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
        $this->setDescription('Update resource income of all players');
        $this->setHelp('Fix incosistencies by updating resource income of all players...');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $output->writeln([
            'Updating income resources of all players',
            '============',
            '',
        ]);

        foreach ($this->worldRepository->findByPublic(true) as $world) {
            $output->writeln("Processing World: {$world->getName()}");

            foreach ($world->getPlayers() as $player) {
                $this->syncPlayerResources($output, $player);
            }
        }

        $output->writeln('Done!');
    }

    /**
     * XXX TODO: Improve function
     *
     * @param OutputInterface $output
     * @param Player $player
     */
    private function syncPlayerResources(OutputInterface $output, Player $player): void
    {
        $upkeepCash = 0;
        $upkeepFood = 0;
        $upkeepWood = 0;
        $upkeepSteel = 0;

        $incomeCash = 0;
        $incomeFood = 0;
        $incomeWood = 0;
        $incomeSteel = 0;

        foreach ($player->getWorldRegions() as $worldRegion) {
            /** @var WorldRegionUnit $worldRgionUnit */
            foreach ($worldRegion->getWorldRegionUnits() as $worldRgionUnit) {
                $upkeepCash += $worldRgionUnit->getAmount() * $worldRgionUnit->getGameUnit()->getUpkeepCash();
                $upkeepFood += $worldRgionUnit->getAmount() * $worldRgionUnit->getGameUnit()->getUpkeepFood();
                $upkeepWood += $worldRgionUnit->getAmount() * $worldRgionUnit->getGameUnit()->getUpkeepWood();
                $upkeepSteel += $worldRgionUnit->getAmount() * $worldRgionUnit->getGameUnit()->getUpkeepSteel();

                $incomeCash += $worldRgionUnit->getAmount() * $worldRgionUnit->getGameUnit()->getIncomeCash();
                $incomeFood += $worldRgionUnit->getAmount() * $worldRgionUnit->getGameUnit()->getIncomeFood();
                $incomeWood += $worldRgionUnit->getAmount() * $worldRgionUnit->getGameUnit()->getIncomeWood();
                $incomeSteel += $worldRgionUnit->getAmount() * $worldRgionUnit->getGameUnit()->getIncomeSteel();
            }
        }

        $changes = false;
        $resources = $player->getResources();

        if ($resources->getUpkeepCash() !== $upkeepCash) {
            $output->writeln("Mismatch found: {$player->getName()} UpkeepCash: {$resources->getUpkeepCash()} => {$upkeepCash}");
            $changes = true;
            $resources->setUpkeepCash($upkeepCash);
        }

        if ($resources->getUpkeepFood() !== $upkeepFood) {
            $output->writeln("Mismatch found: {$player->getName()} UpkeepFood: {$resources->getUpkeepFood()} => {$upkeepFood}");
            $changes = true;
            $resources->setUpkeepFood($upkeepFood);
        }

        if ($resources->getUpkeepWood() !== $upkeepWood) {
            $output->writeln("Mismatch found: {$player->getName()} UpkeepWood: {$resources->getUpkeepWood()} => {$upkeepWood}");
            $changes = true;
            $resources->setUpkeepWood($upkeepWood);
        }

        if ($resources->getUpkeepSteel() !== $upkeepSteel) {
            $output->writeln("Mismatch found: {$player->getName()} UpkeepSteel: {$resources->getUpkeepSteel()} => {$upkeepSteel}");
            $changes = true;
            $resources->setUpkeepSteel($upkeepSteel);
        }

        if ($resources->getIncomeCash() !== $incomeCash) {
            $output->writeln("Mismatch found: {$player->getName()} IncomeCash: {$resources->getIncomeCash()} => {$incomeCash}");
            $changes = true;
            $resources->setIncomeCash($incomeCash);
        }

        if ($resources->getIncomeFood() !== $incomeFood) {
            $output->writeln("Mismatch found: {$player->getName()} IncomeFood: {$resources->getIncomeFood()} => {$incomeFood}");
            $changes = true;
            $resources->setIncomeFood($incomeFood);
        }

        if ($resources->getIncomeWood() !== $incomeWood) {
            $output->writeln("Mismatch found: {$player->getName()} IncomeWood: {$resources->getIncomeWood()} => {$incomeWood}");
            $changes = true;
            $resources->setIncomeWood($incomeWood);
        }

        if ($resources->getIncomeSteel() !== $incomeSteel) {
            $output->writeln("Mismatch found: {$player->getName()} IncomeSteel: {$resources->getIncomeSteel()} => {$incomeSteel}");
            $changes = true;
            $resources->setIncomeSteel($incomeSteel);
        }

        if ($changes) {
            $player->setResources($resources);
            $this->playerRepository->save($player);
        }
    }
}
