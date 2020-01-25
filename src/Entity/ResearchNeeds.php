<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

class ResearchNeeds
{
    private ?int $id;
    private Research $research;
    private Research $requiredResearch;

    public function getId(): int
    {
        return $this->id;
    }

    public function getResearch(): Research
    {
        return $this->research;
    }

    public function setResearch(Research $research): void
    {
        $this->research = $research;
    }

    public function getRequiredResearch(): Research
    {
        return $this->requiredResearch;
    }

    public function setRequiredResearch(Research $requiredResearch): void
    {
        $this->requiredResearch = $requiredResearch;
    }
}
