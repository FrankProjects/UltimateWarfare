<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

/**
 * Message
 */
class Message
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var Player
     */
    private $fromPlayer;

    /**
     * @var bool
     */
    private $fromDelete = false;

    /**
     * @var Player
     */
    private $toPlayer;

    /**
     * @var bool
     */
    private $toDelete = false;

    /**
     * @var bool
     */
    private $toStatus = false;

    /**
     * @var bool
     */
    private $adminMessage = false;

    /**
     * @var string
     */
    private $subject;

    /**
     * @var int
     */
    private $timestamp;

    /**
     * @var string
     */
    private $message;

    /**
     * @var World
     */
    private $world;

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
     * Set fromDelete
     *
     * @param bool $fromDelete
     */
    public function setFromDelete(bool $fromDelete): void
    {
        $this->fromDelete = $fromDelete;
    }

    /**
     * Get fromDelete
     *
     * @return bool
     */
    public function getFromDelete(): bool
    {
        return $this->fromDelete;
    }

    /**
     * Set toDelete
     *
     * @param bool $toDelete
     */
    public function setToDelete(bool $toDelete): void
    {
        $this->toDelete = $toDelete;
    }

    /**
     * Get toDelete
     *
     * @return bool
     */
    public function getToDelete(): bool
    {
        return $this->toDelete;
    }

    /**
     * Set toStatus
     *
     * @param bool $toStatus
     */
    public function setToStatus(bool $toStatus): void
    {
        $this->toStatus = $toStatus;
    }

    /**
     * Get toStatus
     *
     * @return bool
     */
    public function getToStatus(): bool
    {
        return $this->toStatus;
    }

    /**
     * Set adminMessage
     *
     * @param bool $adminMessage
     */
    public function setAdminMessage(bool $adminMessage): void
    {
        $this->adminMessage = $adminMessage;
    }

    /**
     * Get adminMessage
     *
     * @return bool
     */
    public function getAdminMessage(): bool
    {
        return $this->adminMessage;
    }

    /**
     * Set subject
     *
     * @param string $subject
     */
    public function setSubject(string $subject): void
    {
        $this->subject = $subject;
    }

    /**
     * Get subject
     *
     * @return string
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * Set timestamp
     *
     * @param int $timestamp
     */
    public function setTimestamp(int $timestamp): void
    {
        $this->timestamp = $timestamp;
    }

    /**
     * Get timestamp
     *
     * @return int
     */
    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    /**
     * Set message
     *
     * @param string $message
     */
    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return World
     */
    public function getWorld(): World
    {
        return $this->world;
    }

    /**
     * @param World $world
     */
    public function setWorld(World $world): void
    {
        $this->world = $world;
    }

    /**
     * @return Player
     */
    public function getFromPlayer(): Player
    {
        return $this->fromPlayer;
    }

    /**
     * @param Player $fromPlayer
     */
    public function setFromPlayer(Player $fromPlayer): void
    {
        $this->fromPlayer = $fromPlayer;
    }

    /**
     * @return Player
     */
    public function getToPlayer(): Player
    {
        return $this->toPlayer;
    }

    /**
     * @param Player $toPlayer
     */
    public function setToPlayer(Player $toPlayer): void
    {
        $this->toPlayer = $toPlayer;
    }

    /**
     * @param Player $fromPlayer
     * @param Player $toPlayer
     * @param string $subject
     * @param string $content
     * @param bool $adminMessage
     * @return Message
     */
    public static function create(Player $fromPlayer, Player $toPlayer, string $subject, string $content, bool $adminMessage): Message
    {
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
