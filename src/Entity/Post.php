<?php

namespace FrankProjects\UltimateWarfare\Entity;

/**
 * Post
 */
class Post
{
    /**
     * @var int
     */
    private $id;

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
    private $edited = false;

    /**
     * @var Topic
     */
    private $topic;

    /**
     * @var User
     */
    private $user;

    /**
     * @var User|null
     */
    private $editUser;

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
     * Set edited
     *
     * @param bool $edited
     */
    public function setEdited(bool $edited): void
    {
        $this->edited = $edited;
    }

    /**
     * Get edited
     *
     * @return bool
     */
    public function getEdited(): bool
    {
        return $this->edited;
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
     * @return Topic
     */
    public function getTopic(): Topic
    {
        return $this->topic;
    }

    /**
     * @param Topic $topic
     */
    public function setTopic(Topic $topic): void
    {
        $this->topic = $topic;
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
     * @param User|null $editUser
     */
    public function setEditUser(?User $editUser): void
    {
        $this->editUser = $editUser;
    }
}

