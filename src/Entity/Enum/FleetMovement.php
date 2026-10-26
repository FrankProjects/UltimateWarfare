<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity\Enum;

/**
 * How a fleet travels: ground troops march over the player's own land regions, ships sail over water and aircraft
 * fly in a straight line
 */
enum FleetMovement: string
{
    case GROUND = 'ground';
    case NAVAL = 'naval';
    case AIR = 'air';
}
