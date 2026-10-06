<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity\Operation;

use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitEnum;
use FrankProjects\UltimateWarfare\Entity\Operation;
use FrankProjects\UltimateWarfare\Entity\Research\ArtilleryBombardmentResearch;
use FrankProjects\UltimateWarfare\Service\OperationEngine\OperationProcessor\ArtilleryBombardment;

final readonly class ArtilleryBombardmentOperation extends Operation
{
    public function __construct()
    {
        parent::__construct(
            name: 'Artillery Bombardment',
            image: 'op_artillery_bombardment.jpg',
            cost: 25,
            description: "Bombard an adjacent enemy region. Damage is split 75% military units / 25%"
                . " buildings, with diminishing returns at high volumes. No artillery is consumed,"
                . " but each piece enters a 10-minute cooldown after firing."
                . " The enemy always receives a report.",
            enabled: true,
            difficulty: 0,
            maxDistance: 2,
            researchClass: ArtilleryBombardmentResearch::class,
            gameUnit: GameUnitEnum::ARTILLERY,
            researchMinLevel: 1,
        );
    }

    public function getSlug(): string
    {
        return 'artillery-bombardment';
    }

    public function getProcessorClass(): string
    {
        return ArtilleryBombardment::class;
    }

    public function hasCooldown(): bool
    {
        return true;
    }
}
