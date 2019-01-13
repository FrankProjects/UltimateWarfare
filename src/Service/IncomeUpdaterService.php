<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service;

use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Repository\PlayerRepository;
use FrankProjects\UltimateWarfare\Util\IncomeCalculator;
use FrankProjects\UltimateWarfare\Util\UpkeepCalculator;

final class IncomeUpdaterService
{
    /**
     * @var PlayerRepository
     */
    private $playerRepository;

    /**
     * @var IncomeCalculator
     */
    private $incomeCalculator;

    /**
     * @var UpkeepCalculator
     */
    private $upkeepCalculator;

    /**
     * IncomeUpdaterService constructor.
     *
     * @param PlayerRepository $playerRepository
     * @param IncomeCalculator $incomeCalculator
     * @param UpkeepCalculator $upkeepCalculator
     */
    public function __construct(
        PlayerRepository $playerRepository,
        IncomeCalculator $incomeCalculator,
        UpkeepCalculator $upkeepCalculator
    ) {
        $this->playerRepository = $playerRepository;
        $this->incomeCalculator = $incomeCalculator;
        $this->upkeepCalculator = $upkeepCalculator;
    }

    /**
     * @param Player $player
     */
    public function updateIncomeForPlayer(Player $player): void
    {
        $income = $this->incomeCalculator->calculateIncomeForPlayer($player);
        $upkeep = $this->upkeepCalculator->calculateUpkeepForPlayer($player);

        $player->setIncome($income);
        $player->setUpkeep($upkeep);

        $this->playerRepository->save($player);
    }
}
