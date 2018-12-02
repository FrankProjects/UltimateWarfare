<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

/**
 * UnbanRequest
 */
class UnbanRequest
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var User
     */
    private $user;

    /**
     * @var string
     */
    private $post = '';

    /**
     * @var int
     */
    private $status = 0;


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
     * Set User
     *
     * @param User $user
     */
    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    /**
     * Get User
     *
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * Set post
     *
     * @param string $post
     */
    public function setPost(string $post): void
    {
        $this->post = $post;
    }

    /**
     * Get post
     *
     * @return string
     */
    public function getPost(): string
    {
        return $this->post;
    }

    /**
     * Set stat
     *
     * @param int $status
     */
    public function setStatus(int $status): void
    {
        $this->status = $status;
    }

    /**
     * Get status
     *
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }
}
