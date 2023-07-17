<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

class Message
{
    public const MESSAGE_STATUS_NEW = 0;
    public const MESSAGE_STATUS_READ = 1;

    private ?int $id;
    private Player $fromPlayer;
    private bool $fromDelete = false;
    private Player $toPlayer;
    private bool $toDelete = false;
    private int $status = 0;
    private bool $adminMessage = false;
    private string $subject;
    private int $timestamp;
    private string $message;
    private World $world;

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setFromDelete(bool $fromDelete): void
    {
        $this->fromDelete = $fromDelete;
    }

    public function getFromDelete(): bool
    {
        return $this->fromDelete;
    }

    public function setToDelete(bool $toDelete): void
    {
        $this->toDelete = $toDelete;
    }

    public function getToDelete(): bool
    {
        return $this->toDelete;
    }

    public function setStatus(int $status): void
    {
        $this->status = $status;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setAdminMessage(bool $adminMessage): void
    {
        $this->adminMessage = $adminMessage;
    }

    public function getAdminMessage(): bool
    {
        return $this->adminMessage;
    }

    public function setSubject(string $subject): void
    {
        $this->subject = $subject;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function setTimestamp(int $timestamp): void
    {
        $this->timestamp = $timestamp;
    }

    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getWorld(): World
    {
        return $this->world;
    }

    public function setWorld(World $world): void
    {
        $this->world = $world;
    }

    public function getFromPlayer(): Player
    {
        return $this->fromPlayer;
    }

    public function setFromPlayer(Player $fromPlayer): void
    {
        $this->fromPlayer = $fromPlayer;
    }

    public function getToPlayer(): Player
    {
        return $this->toPlayer;
    }

    public function setToPlayer(Player $toPlayer): void
    {
        $this->toPlayer = $toPlayer;
    }

    public static function create(
        Player $fromPlayer,
        Player $toPlayer,
        string $subject,
        string $content,
        bool $adminMessage
    ): Message {
        $message = new Message();
        $message->setFromPlayer($fromPlayer);
        $message->setToPlayer($toPlayer);
        $message->setTimestamp(time());
        $message->setSubject($subject);
        $message->setMessage($content);
        $message->setAdminMessage($adminMessage);
        $message->setWorld($fromPlayer->getWorld());

        return $message;
    }
}
