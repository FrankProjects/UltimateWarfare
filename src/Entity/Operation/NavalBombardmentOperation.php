<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity\Operation;

use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitEnum;
use FrankProjects\UltimateWarfare\Entity\Operation;
use FrankProjects\UltimateWarfare\Entity\Research\NavalBombardmentResearch;
use FrankProjects\UltimateWarfare\Service\OperationEngine\OperationProcessor\NavalBombardment;

final readonly class NavalBombardmentOperation extends Operation
{
    public function __construct()
    {
        parent::__construct(
            name: 'Naval Bombardment',
            image: 'op_naval_bombardment.jpg',
            cost: 25,
            description: "Bombard a coastal enemy region with cruisers. Damage is split 75% military units"
                . " / 25% buildings, with diminishing returns at high volumes. Cruisers are not consumed"
                . " but enter a 10-minute cooldown after firing."
                . " The enemy always receives a report.",
            enabled: true,
            difficulty: 0,
            maxDistance: 3,
            researchClass: NavalBombardmentResearch::class,
            gameUnit: GameUnitEnum::CRUISER,
        );
    }

    public function getSlug(): string
    {
        return 'naval-bombardment';
    }

    public function getProcessorClass(): string
    {
        return NavalBombardment::class;
    }

    public function hasCooldown(): bool
    {
        return true;
    }
}
