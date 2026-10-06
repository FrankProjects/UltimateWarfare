<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Twig;

use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitEnum;
use FrankProjects\UltimateWarfare\Entity\GameUnit;
use FrankProjects\UltimateWarfare\Repository\GameUnitRegistry;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class GameUnitExtension extends AbstractExtension
{
    public function __construct(
        private GameUnitRegistry $gameUnitRegistry
    ) {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('game_unit', [$this, 'resolveGameUnit']),
        ];
    }

    public function resolveGameUnit(GameUnitEnum $gameUnitEnum): GameUnit
    {
        return $this->gameUnitRegistry->find($gameUnitEnum);
    }
}
