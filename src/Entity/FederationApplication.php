<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

class FederationApplication
{
    private ?int $id;
    private string $application;
    private Federation $federation;
    private Player $player;

    public function getId(): int
    {
        return $this->id;
    }

    public function setApplication(string $application): void
    {
        $this->application = $application;
    }

    public function getApplication(): string
    {
        return $this->application;
    }

    public function getFederation(): Federation
    {
        return $this->federation;
    }

    public function setFederation(Federation $federation): void
    {
        $this->federation = $federation;
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function setPlayer(Player $player): void
    {
        $this->player = $player;
    }

    public static function createForFederation(
        Federation $federation,
        Player $player,
        string $application
    ): FederationApplication {
        $federationApplication = new FederationApplication();
        $federationApplication->setFederation($federation);
        $federationApplication->setPlayer($player);
        $federationApplication->setApplication($application);

        return $federationApplication;
    }
}
