<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Exception;

final class WorldRegionNotFoundException extends \Exception
{
    #[\Override]
    protected $message = 'World region does not exist!';
}
