<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity\Operation;

use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitEnum;
use FrankProjects\UltimateWarfare\Entity\Operation;
use FrankProjects\UltimateWarfare\Entity\Research\BallisticMissileTechnologyResearch;
use FrankProjects\UltimateWarfare\Service\OperationEngine\OperationProcessor\MissileAttack;

final readonly class MissileAttackOperation extends Operation
{
    public function __construct()
    {
        parent::__construct(
            name: 'Missile Attack',
            image: 'op_missile_attack.jpg',
            cost: 50,
            description: "Launch ballistic missiles at an enemy region. On average, each missile destroys"
                . " half a building (regular buildings only, not special ones)."
                . " The enemy always receives a report and sees your name.",
            enabled: true,
            difficulty: 0.5,
            maxDistance: 3,
            researchClass: BallisticMissileTechnologyResearch::class,
            gameUnit: GameUnitEnum::ROCKET,
        );
    }

    public function getSlug(): string
    {
        return 'missile-attack';
    }

    public function getProcessorClass(): string
    {
        return MissileAttack::class;
    }
}
