<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

/**
 * XXX TODO: Fix model & relations
 */
class Operation
{
    private ?int $id;
    private int $needs;
    private string $name;
    private string $pic;
    private int $unitId;
    private int $cost;
    private int $tick;
    private string $description;
    private bool $active = true;
    private float $difficulty = 0.5;
    private int $maxDistance;

    public function getId(): int
    {
        return $this->id;
    }
}
