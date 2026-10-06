<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity\Operation;

use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitEnum;
use FrankProjects\UltimateWarfare\Entity\Operation;
use FrankProjects\UltimateWarfare\Entity\Research\SubmarineTechnologyResearch;
use FrankProjects\UltimateWarfare\Service\OperationEngine\OperationProcessor\SubmarineAttack;

final readonly class SubmarineAttackOperation extends Operation
{
    public function __construct()
    {
        parent::__construct(
            name: 'Submarine Attack',
            image: 'op_submarine_attack.jpg',
            cost: 25000,
            description: "Send submarines to sink enemy destroyers. Each submarine sinks 1 destroyer."
                . " On failure, you lose 5% of the deployed submarines."
                . " The enemy always receives a report; your name is hidden on success.",
            enabled: true,
            difficulty: 0.5,
            maxDistance: 4,
            researchClass: SubmarineTechnologyResearch::class,
            gameUnit: GameUnitEnum::SUBMARINE,
        );
    }

    public function getSlug(): string
    {
        return 'submarine-attack';
    }

    public function getProcessorClass(): string
    {
        return SubmarineAttack::class;
    }
}
