<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity\Operation;

use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitEnum;
use FrankProjects\UltimateWarfare\Entity\Operation;
use FrankProjects\UltimateWarfare\Entity\Research\BomberAttackResearch;
use FrankProjects\UltimateWarfare\Service\OperationEngine\OperationProcessor\BomberAttack;

final readonly class BomberAttackOperation extends Operation
{
    public function __construct()
    {
        parent::__construct(
            name: 'Bomber Attack',
            image: 'op_bomber_attack.jpg',
            cost: 15000,
            description: "Send bombers to destroy enemy infrastructure. Each bomber destroys 5 special"
                . " buildings (train stations, airfields, harbors), distributed proportionally across"
                . " what's available. On failure, you lose 5% of the deployed bombers."
                . " The enemy always receives a report; your name is hidden on success.",
            enabled: true,
            difficulty: 0.5,
            maxDistance: 4,
            researchClass: BomberAttackResearch::class,
            gameUnit: GameUnitEnum::BOMBER,
            researchMinLevel: 1,
        );
    }

    public function getSlug(): string
    {
        return 'bomber-attack';
    }

    public function getProcessorClass(): string
    {
        return BomberAttack::class;
    }
}
