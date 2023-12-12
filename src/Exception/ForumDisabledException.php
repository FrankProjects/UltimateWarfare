<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Exception;

final class ForumDisabledException extends \Exception
{
    protected $message = 'Forum is disabled!'; // @phpstan-ignore-line
}
