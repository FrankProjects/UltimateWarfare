<?php

namespace FrankProjects\UltimateWarfare\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * GameAccount
 */
class GameAccount
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $masterId;

    /**
     * @var int
     */
    private $level = 1;

    /**
     * @var int
     */
    private $signup;

    /**
     * @var string
     */
    private $ip;

    /**
     * @var bool
     */
    private $active = true;

    /**
     * @var bool
     */
    private $hints = true;

    /**
     * @var string
     */
    private $forumName;

    /**
     * @var int
     */
    private $forumLastpost = 0;

    /**
     * @var bool
     */
    private $forumBan = false;

    /**
     * @var bool
     */
    private $adviser = false;

    /**
     * @var MapDesign
     */
    private $mapDesign;

    /**
     * @var Collection|Player[]
     */
    private $players = [];

    /**
     * @var Collection|Topic[]
     */
    private $topics = [];

    /**
     * @var Collection|Topic[]
     */
    private $topicsEdited = [];

    /**
     * @var Collection|Topic[]
     */
    private $topicsLastPost = [];

    /**
     * @var Collection|Topic[]
     */
    private $posts = [];

    /**
     * @var Collection|Topic[]
     */
    private $postsEdited = [];

    /**
     * GameAccount constructor.
     */
    public function __construct()
    {
        $this->players = new ArrayCollection();
        $this->topics = new ArrayCollection();
        $this->topicsEdited = new ArrayCollection();
        $this->topicsLastPost = new ArrayCollection();
        $this->posts = new ArrayCollection();
        $this->postsEdited = new ArrayCollection();
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
     * Set masterId
     *
     * @param int $masterId
     */
    public function setMasterId(int $masterId)
    {
        $this->masterId = $masterId;
    }

    /**
     * Get masterId
     *
     * @return int
     */
    public function getMasterId(): int
    {
        return $this->masterId;
    }

    /**
     * Set level
     *
     * @param int $level
     */
    public function setLevel(int $level)
    {
        $this->level = $level;
    }

    /**
     * Get level
     *
     * @return int
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * Set signup
     *
     * @param int $signup
     */
    public function setSignup(int $signup)
    {
        $this->signup = $signup;
    }

    /**
     * Get signup
     *
     * @return int
     */
    public function getSignup()
    {
        return $this->signup;
    }

    /**
     * Set ip
     *
     * @param string $ip
     *
     * @return GameAccount
     */
    public function setIp($ip)
    {
        $this->ip = $ip;

        return $this;
    }

    /**
     * Get ip
     *
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * Set active
     *
     * @param bool $active
     */
    public function setActive(bool $active)
    {
        $this->active = $active;
    }

    /**
     * Get active
     *
     * @return bool
     */
    public function getActive(): bool
    {
        return $this->active;
    }

    /**
     * Set hints
     *
     * @param bool $hints
     */
    public function setHints(bool $hints)
    {
        $this->hints = $hints;
    }

    /**
     * Get hints
     *
     * @return bool
     */
    public function getHints(): bool
    {
        return $this->hints;
    }

    /**
     * Set forumName
     *
     * @param string $forumName
     */
    public function setForumName(string $forumName)
    {
        $this->forumName = $forumName;
    }

    /**
     * Get forumName
     *
     * @return string
     */
    public function getForumName(): string
    {
        return $this->forumName;
    }

    /**
     * Set forumLastpost
     *
     * @param int $forumLastpost
     */
    public function setForumLastpost(int $forumLastpost)
    {
        $this->forumLastpost = $forumLastpost;
    }

    /**
     * Get forumLastpost
     *
     * @return int
     */
    public function getForumLastpost(): int
    {
        return $this->forumLastpost;
    }

    /**
     * Set forumBan
     *
     * @param bool $forumBan
     */
    public function setForumBan(bool $forumBan)
    {
        $this->forumBan = $forumBan;
    }

    /**
     * Get forumBan
     *
     * @return bool
     */
    public function getForumBan(): bool
    {
        return $this->forumBan;
    }

    /**
     * Set adviser
     *
     * @param bool $adviser
     */
    public function setAdviser(bool $adviser)
    {
        $this->adviser = $adviser;
    }

    /**
     * Get adviser
     *
     * @return bool
     */
    public function getAdviser(): bool
    {
        return $this->adviser;
    }

    /**
     * @return Collection
     */
    public function getPlayers(): Collection
    {
        return $this->players;
    }

    /**
     * @param Collection $players
     */
    public function setPlayers(Collection $players)
    {
        $this->players = $players;
    }

    /**
     * @return MapDesign
     */
    public function getMapDesign(): MapDesign
    {
        return $this->mapDesign;
    }

    /**
     * @param MapDesign $mapDesign
     */
    public function setMapDesign(MapDesign $mapDesign)
    {
        $this->mapDesign = $mapDesign;
    }

    /**
     * @return Collection
     */
    public function getTopics(): Collection
    {
        return $this->topics;
    }

    /**
     * @param Collection $topics
     */
    public function setTopics(Collection $topics)
    {
        $this->topics = $topics;
    }

    /**
     * @return Collection
     */
    public function getTopicsEdited(): Collection
    {
        return $this->topicsEdited;
    }

    /**
     * @param Collection $topicsEdited
     */
    public function setTopicsEdited(Collection $topicsEdited)
    {
        $this->topicsEdited = $topicsEdited;
    }

    /**
     * @return Collection
     */
    public function getTopicsLastPost(): Collection
    {
        return $this->topicsLastPost;
    }

    /**
     * @param Collection $topicsLastPost
     */
    public function setTopicsLastPost(Collection $topicsLastPost)
    {
        $this->topicsLastPost = $topicsLastPost;
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
    public function setPosts(Collection $posts)
    {
        $this->posts = $posts;
    }

    /**
     * @return Collection
     */
    public function getPostsEdited(): Collection
    {
        return $this->postsEdited;
    }

    /**
     * @param Collection $postsEdited
     */
    public function setPostsEdited(Collection $postsEdited)
    {
        $this->postsEdited = $postsEdited;
    }

    /**
     * @param int $masterId
     * @param string $ipAddress
     * @param MapDesign $mapDesign
     * @return GameAccount
     */
    public static function create(int $masterId, string $ipAddress, MapDesign $mapDesign): GameAccount
    {
        $gameAccount = new GameAccount();
        $gameAccount->setMasterId($masterId);
        $gameAccount->setSignup(time());
        $gameAccount->setIp($ipAddress);
        $gameAccount->setMapDesign($mapDesign);

        return $gameAccount;
    }
}
