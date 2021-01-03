<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\GameEngine;

interface Processor
{
    public function run(int $timestamp): void;
}
