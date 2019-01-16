<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Topic
 */
class Topic
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $title = '';

    /**
     * @var string
     */
    private $posterIp;

    /**
     * @var string
     */
    private $content = '';

    /**
     * @var \DateTime
     */
    private $createDateTime;

    /**
     * @var bool
     */
    private $sticky = false;

    /**
     * @var bool
     */
    private $closed = false;

    /**
     * @var Category
     */
    private $category;

    /**
     * @var User
     */
    private $user;

    /**
     * @var User|null
     */
    private $editUser;

    /**
     * @var Collection|Post[]
     */
    private $posts = [];

    /**
     * Topic constructor.
     */
    public function __construct()
    {
        $this->posts = new ArrayCollection();
    }

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
     * Set title
     *
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Set posterIp
     *
     * @param string $posterIp
     */
    public function setPosterIp(string $posterIp): void
    {
        $this->posterIp = $posterIp;
    }

    /**
     * Get posterIp
     *
     * @return string
     */
    public function getPosterIp(): string
    {
        return $this->posterIp;
    }

    /**
     * Set sticky
     *
     * @param bool $sticky
     */
    public function setSticky(bool $sticky): void
    {
        $this->sticky = $sticky;
    }

    /**
     * Get sticky
     *
     * @return bool
     */
    public function getSticky(): bool
    {
        return $this->sticky;
    }

    /**
     * Set closed
     *
     * @param bool $closed
     */
    public function setClosed(bool $closed): void
    {
        $this->closed = $closed;
    }

    /**
     * Get closed
     *
     * @return bool
     */
    public function getClosed(): bool
    {
        return $this->closed;
    }

    /**
     * Set content
     *
     * @param string $content
     */
    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @return Category
     */
    public function getCategory(): Category
    {
        return $this->category;
    }

    /**
     * @param Category $category
     */
    public function setCategory(Category $category): void
    {
        $this->category = $category;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    /**
     * @return User|null
     */
    public function getEditUser(): ?User
    {
        return $this->editUser;
    }

    /**
     * @param User $editUser
     */
    public function setEditUser(User $editUser): void
    {
        $this->editUser = $editUser;
    }

    /**
     * @return \DateTime
     */
    public function getCreateDateTime(): \DateTime
    {
        return $this->createDateTime;
    }

    /**
     * @param \DateTime $createDateTime
     */
    public function setCreateDateTime(\DateTime $createDateTime): void
    {
        $this->createDateTime = $createDateTime;
    }

    /**
     * @return Collection
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    /**
     * @param Collection $posts
     */
    public function setPosts(Collection $posts): void
    {
        $this->posts = $posts;
    }
}
