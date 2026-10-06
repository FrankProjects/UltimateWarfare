<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity\Operation;

use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitEnum;
use FrankProjects\UltimateWarfare\Entity\Operation;
use FrankProjects\UltimateWarfare\Entity\Research\StrategicBomberAttackResearch;
use FrankProjects\UltimateWarfare\Service\OperationEngine\OperationProcessor\StrategicBomberAttack;

final readonly class StrategicBomberAttackOperation extends Operation
{
    public function __construct()
    {
        parent::__construct(
            name: 'Strategic Bomber Attack',
            image: 'op_strategic_bomber_attack.jpg',
            cost: 45000,
            description: "Send strategic bombers on a long-range strike. Each bomber destroys 5 special"
                . " buildings (train stations, airfields, harbors). Same mechanics as Bomber Attack but"
                . " with much greater range. On failure, you lose 5% of the deployed bombers."
                . " The enemy always receives a report; your name is hidden on success.",
            enabled: true,
            difficulty: 0.5,
            maxDistance: 10,
            researchClass: StrategicBomberAttackResearch::class,
            gameUnit: GameUnitEnum::STRATEGIC_BOMBER,
            researchMinLevel: 1,
        );
    }

    public function getSlug(): string
    {
        return 'strategic-bomber-attack';
    }

    public function getProcessorClass(): string
    {
        return StrategicBomberAttack::class;
    }
}
