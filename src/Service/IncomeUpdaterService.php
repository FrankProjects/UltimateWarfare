<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service;

use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Repository\PlayerRepository;
use FrankProjects\UltimateWarfare\Util\IncomeCalculator;

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
     * IncomeUpdaterService constructor.
     *
     * @param PlayerRepository $playerRepository
     * @param IncomeCalculator $incomeCalculator
     */
    public function __construct(
        PlayerRepository $playerRepository,
        IncomeCalculator $incomeCalculator
    ) {
        $this->playerRepository = $playerRepository;
        $this->incomeCalculator = $incomeCalculator;
    }

    /**
     * @param Player $player
     */
    public function updateIncomeForPlayer(Player $player): void
    {
        $resources = $this->incomeCalculator->calculateIncomeForPlayer($player);
        $player->setResources($resources);

        $this->playerRepository->save($player);
    }
}
