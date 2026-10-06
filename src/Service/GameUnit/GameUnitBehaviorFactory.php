<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\GameUnit;

use FrankProjects\UltimateWarfare\Entity\GameUnit;
use FrankProjects\UltimateWarfare\Service\GameUnit\Behavior\AirUnitBehavior;
use FrankProjects\UltimateWarfare\Service\GameUnit\Behavior\DefaultUnitBehavior;
use FrankProjects\UltimateWarfare\Service\GameUnit\Behavior\NavalUnitBehavior;

class GameUnitBehaviorFactory
{
    /** @var array<string, GameUnitBehaviorInterface> */
    private array $behaviors = [];

    public function __construct(
        DefaultUnitBehavior $defaultBehavior,
        NavalUnitBehavior $navalBehavior,
        AirUnitBehavior $airBehavior
    ) {
        $this->behaviors[DefaultUnitBehavior::class] = $defaultBehavior;
        $this->behaviors[NavalUnitBehavior::class] = $navalBehavior;
        $this->behaviors[AirUnitBehavior::class] = $airBehavior;
    }

    public function create(GameUnit $gameUnit): GameUnitBehaviorInterface
    {
        $behaviorClass = $gameUnit->getBehaviorClass();

        if ($behaviorClass === null || $behaviorClass === '') {
            return $this->behaviors[DefaultUnitBehavior::class];
        }

        if (!isset($this->behaviors[$behaviorClass])) {
            throw new \RuntimeException("Behavior class not registered: {$behaviorClass}");
        }

        return $this->behaviors[$behaviorClass];
    }
}
