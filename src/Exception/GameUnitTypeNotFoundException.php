<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Exception;

final class GameUnitTypeNotFoundException extends \Exception
{
    protected $message = 'Game unit type does not exist!'; // @phpstan-ignore-line
}
