<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

class UnbanRequest
{
    private ?int $id;
    private User $user;
    private string $post = '';
    private int $status = 0;

    public function getId(): int
    {
        return $this->id;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setPost(string $post): void
    {
        $this->post = $post;
    }

    public function getPost(): string
    {
        return $this->post;
    }

    public function setStatus(int $status): void
    {
        $this->status = $status;
    }

    public function getStatus(): int
    {
        return $this->status;
    }
}
