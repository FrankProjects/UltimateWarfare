<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

use DateTime;

class Post
{
    private ?int $id;
    private string $posterIp;
    private string $content = '';
    private DateTime $createDateTime;
    private bool $edited = false;
    private Topic $topic;
    private User $user;
    private ?User $editUser;

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setPosterIp(string $posterIp): void
    {
        $this->posterIp = $posterIp;
    }

    public function getPosterIp(): string
    {
        return $this->posterIp;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setEdited(bool $edited): void
    {
        $this->edited = $edited;
    }

    public function getEdited(): bool
    {
        return $this->edited;
    }

    public function getCreateDateTime(): DateTime
    {
        return $this->createDateTime;
    }

    public function setCreateDateTime(DateTime $createDateTime): void
    {
        $this->createDateTime = $createDateTime;
    }

    public function getTopic(): Topic
    {
        return $this->topic;
    }

    public function setTopic(Topic $topic): void
    {
        $this->topic = $topic;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getEditUser(): ?User
    {
        return $this->editUser;
    }

    public function setEditUser(?User $editUser): void
    {
        $this->editUser = $editUser;
    }
}
