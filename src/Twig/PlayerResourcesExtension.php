<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Twig;

use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\Player\Resources;
use FrankProjects\UltimateWarfare\Util\ResourceAccrualCalculator;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Stored resources are only written when the player is synced; this shows their current value without a write.
 */
class PlayerResourcesExtension extends AbstractExtension
{
    public function __construct(
        private ResourceAccrualCalculator $resourceAccrualCalculator
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('player_resources', [$this, 'getCurrentResources']),
        ];
    }

    public function getCurrentResources(Player $player, ?int $at = null): Resources
    {
        return $this->resourceAccrualCalculator->project($player, $at ?? time());
    }
}
