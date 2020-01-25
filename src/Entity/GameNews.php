<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

use DateTime;
use Exception;

class GameNews
{
    private ?int $id;
    private string $title = '';
    private string $message = '';
    private DateTime $createDateTime;
    private bool $enabled = false;
    private bool $mainpage = false;

    public function __construct()
    {
        try {
            $this->createDateTime = new DateTime();
        } catch (Exception $e) {
        }
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    public function getEnabled(): bool
    {
        return $this->enabled;
    }

    public function setMainpage(bool $mainpage): void
    {
        $this->mainpage = $mainpage;
    }

    public function getMainpage(): bool
    {
        return $this->mainpage;
    }

    public function getCreateDateTime(): DateTime
    {
        return $this->createDateTime;
    }

    public function setCreateDateTime(DateTime $createDateTime): void
    {
        $this->createDateTime = $createDateTime;
    }
}
