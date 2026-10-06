<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity\Operation;

use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitEnum;
use FrankProjects\UltimateWarfare\Entity\Operation;
use FrankProjects\UltimateWarfare\Entity\Research\AdvancedOpticsResearch;
use FrankProjects\UltimateWarfare\Service\OperationEngine\OperationProcessor\SniperAttack;

final readonly class SniperAttackOperation extends Operation
{
    public function __construct()
    {
        parent::__construct(
            name: 'Sniper Team',
            image: 'op_sniper_attack.jpg',
            cost: 250,
            description: "Deploy snipers behind enemy lines. Each sniper kills 5 enemy soldiers."
                . " On failure, you lose 5% of the deployed snipers."
                . " The enemy always receives a report; your name is hidden on success.",
            enabled: true,
            difficulty: 0.5,
            maxDistance: 2,
            researchClass: AdvancedOpticsResearch::class,
            gameUnit: GameUnitEnum::SNIPER,
        );
    }

    public function getSlug(): string
    {
        return 'sniper-attack';
    }

    public function getProcessorClass(): string
    {
        return SniperAttack::class;
    }
}
