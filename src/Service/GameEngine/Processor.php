<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\GameEngine;

interface Processor
{
    /**
     * @param int $timestamp
     */
    public function run(int $timestamp): void;
}
