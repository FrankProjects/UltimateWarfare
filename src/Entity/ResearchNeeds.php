<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

/**
 * ResearchNeeds
 */
class ResearchNeeds
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var Research
     */
    private $research;

    /**
     * @var Research
     */
    private $requiredResearch;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return Research
     */
    public function getResearch(): Research
    {
        return $this->research;
    }

    /**
     * @param Research $research
     */
    public function setResearch(Research $research)
    {
        $this->research = $research;
    }

    /**
     * @return Research
     */
    public function getRequiredResearch(): Research
    {
        return $this->requiredResearch;
    }

    /**
     * @param Research $requiredResearch
     */
    public function setRequiredResearch(Research $requiredResearch)
    {
        $this->requiredResearch = $requiredResearch;
    }
}
