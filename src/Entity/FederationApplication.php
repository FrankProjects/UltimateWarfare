<?php

namespace FrankProjects\UltimateWarfare\Entity;

/**
 * FederationApplication
 */
class FederationApplication
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $application;

    /**
     * @var Federation
     */
    private $federation;

    /**
     * @var Player
     */
    private $player;

    /**
     * Get id
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Set application
     *
     * @param string $application
     */
    public function setApplication(string $application)
    {
        $this->application = $application;
    }

    /**
     * Get application
     *
     * @return string
     */
    public function getApplication(): string
    {
        return $this->application;
    }

    /**
     * @return Federation
     */
    public function getFederation(): Federation
    {
        return $this->federation;
    }

    /**
     * @param Federation $federation
     */
    public function setFederation(Federation $federation)
    {
        $this->federation = $federation;
    }

    /**
     * @return Player
     */
    public function getPlayer(): Player
    {
        return $this->player;
    }

    /**
     * @param Player $player
     */
    public function setPlayer(Player $player)
    {
        $this->player = $player;
    }
}
