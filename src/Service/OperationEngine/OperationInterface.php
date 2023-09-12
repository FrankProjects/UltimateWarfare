<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\OperationEngine;

interface OperationInterface
{
    /**
     * @return array<int, string>
     */
    public function execute(): array;
    public function getFormula(): float;
    public function processPreOperation(): void;
    public function processSuccess(): void;
    public function processFailed(): void;
    public function processPostOperation(): void;
}
