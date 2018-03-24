<?php

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
     * @var GameAccount
     */
    private $gameAccount;

    /**
     * @var string
     */
    private $post;

    /**
     * @var int
     */
    private $status = '0';


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
     * Set GameAccount
     *
     * @param GameAccount $gameAccount
     */
    public function setGameAccount(GameAccount $gameAccount)
    {
        $this->gameAccount = $gameAccount;
    }

    /**
     * Get GameAccount
     *
     * @return GameAccount
     */
    public function getGameAccount(): GameAccount
    {
        return $this->gameAccount;
    }

    /**
     * Set post
     *
     * @param string $post
     */
    public function setPost(string $post)
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
    public function setStatus(int $status)
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
