<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Topic
{
    private ?int $id;
    private string $title = '';
    private string $posterIp;
    private string $content = '';
    private DateTime $createDateTime;
    private bool $sticky = false;
    private bool $closed = false;
    private Category $category;
    private User $user;
    private ?User $editUser;

    /** @var Collection<int, Post> */
    private Collection $posts;

    public function __construct()
    {
        $this->posts = new ArrayCollection();
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
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

    public function setPosterIp(string $posterIp): void
    {
        $this->posterIp = $posterIp;
    }

    public function getPosterIp(): string
    {
        return $this->posterIp;
    }

    public function setSticky(bool $sticky): void
    {
        $this->sticky = $sticky;
    }

    public function getSticky(): bool
    {
        return $this->sticky;
    }

    public function setClosed(bool $closed): void
    {
        $this->closed = $closed;
    }

    public function getClosed(): bool
    {
        return $this->closed;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getCategory(): Category
    {
        return $this->category;
    }

    public function setCategory(Category $category): void
    {
        $this->category = $category;
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

    public function setEditUser(User $editUser): void
    {
        $this->editUser = $editUser;
    }

    public function getCreateDateTime(): DateTime
    {
        return $this->createDateTime;
    }

    public function setCreateDateTime(DateTime $createDateTime): void
    {
        $this->createDateTime = $createDateTime;
    }

    /**
     * @return Collection<int, Post>
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    /**
     * @param Collection<int, Post> $posts
     */
    public function setPosts(Collection $posts): void
    {
        $this->posts = $posts;
    }
}
