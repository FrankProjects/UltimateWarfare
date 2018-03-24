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
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set masterId
     *
     * @param int $masterId
     *
     * @return GameAccount
     */
    public function setMasterId($masterId)
    {
        $this->masterId = $masterId;

        return $this;
    }

    /**
     * Get masterId
     *
     * @return int
     */
    public function getMasterId()
    {
        return $this->masterId;
    }

    /**
     * Set level
     *
     * @param bool $level
     *
     * @return GameAccount
     */
    public function setLevel($level)
    {
        $this->level = $level;

        return $this;
    }

    /**
     * Get level
     *
     * @return bool
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * Set signup
     *
     * @param int $signup
     *
     * @return GameAccount
     */
    public function setSignup($signup)
    {
        $this->signup = $signup;

        return $this;
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
     *
     * @return GameAccount
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return bool
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set hints
     *
     * @param bool $hints
     *
     * @return GameAccount
     */
    public function setHints($hints)
    {
        $this->hints = $hints;

        return $this;
    }

    /**
     * Get hints
     *
     * @return bool
     */
    public function getHints()
    {
        return $this->hints;
    }

    /**
     * Set forumName
     *
     * @param string $forumName
     *
     * @return GameAccount
     */
    public function setForumName($forumName)
    {
        $this->forumName = $forumName;

        return $this;
    }

    /**
     * Get forumName
     *
     * @return string
     */
    public function getForumName()
    {
        return $this->forumName;
    }

    /**
     * Set forumLastpost
     *
     * @param int $forumLastpost
     *
     * @return GameAccount
     */
    public function setForumLastpost($forumLastpost)
    {
        $this->forumLastpost = $forumLastpost;

        return $this;
    }

    /**
     * Get forumLastpost
     *
     * @return int
     */
    public function getForumLastpost()
    {
        return $this->forumLastpost;
    }

    /**
     * Set forumBan
     *
     * @param bool $forumBan
     *
     * @return GameAccount
     */
    public function setForumBan($forumBan)
    {
        $this->forumBan = $forumBan;

        return $this;
    }

    /**
     * Get forumBan
     *
     * @return bool
     */
    public function getForumBan()
    {
        return $this->forumBan;
    }

    /**
     * Set adviser
     *
     * @param bool $adviser
     *
     * @return GameAccount
     */
    public function setAdviser($adviser)
    {
        $this->adviser = $adviser;

        return $this;
    }

    /**
     * Get adviser
     *
     * @return bool
     */
    public function getAdviser()
    {
        return $this->adviser;
    }

    /**
     * @param int $masterId
     * @param string $ipAddress
     * @param MapDesign $mapDesign
     * @return GameAccount
     */
    public static function create($masterId, $ipAddress, MapDesign $mapDesign)
    {
        $gameAccount = new GameAccount();
        $gameAccount->setMasterId($masterId);
        $gameAccount->setSignup(time());
        $gameAccount->setIp($ipAddress);
        $gameAccount->setMapDesign($mapDesign);

        return $gameAccount;
    }

    /**
     * @return array
     */
    public function getPlayers()
    {
        return $this->players;
    }

    /**
     * @param array $players
     */
    public function setPlayers($players)
    {
        $this->players = $players;
    }

    /**
     * @return MapDesign
     */
    public function getMapDesign()
    {
        return $this->mapDesign;
    }

    /**
     * @param MapDesign $mapDesign
     */
    public function setMapDesign($mapDesign)
    {
        $this->mapDesign = $mapDesign;
    }

    /**
     * @return Collection|Topic[]
     */
    public function getTopics(): Collection
    {
        return $this->topics;
    }

    /**
     * @param Collection|Topic[] $topics
     */
    public function setTopics(Collection $topics)
    {
        $this->topics = $topics;
    }

    /**
     * @return Collection|Topic[]
     */
    public function getTopicsEdited(): Collection
    {
        return $this->topicsEdited;
    }

    /**
     * @param Collection|Topic[] $topicsEdited
     */
    public function setTopicsEdited(Collection $topicsEdited)
    {
        $this->topicsEdited = $topicsEdited;
    }

    /**
     * @return Collection|Topic[]
     */
    public function getTopicsLastPost(): Collection
    {
        return $this->topicsLastPost;
    }

    /**
     * @param Collection|Topic[] $topicsLastPost
     */
    public function setTopicsLastPost(Collection $topicsLastPost)
    {
        $this->topicsLastPost = $topicsLastPost;
    }

    /**
     * @return Collection|Topic[]
     */
    public function getPosts()
    {
        return $this->posts;
    }

    /**
     * @param Collection|Topic[] $posts
     */
    public function setPosts($posts)
    {
        $this->posts = $posts;
    }

    /**
     * @return Collection|Topic[]
     */
    public function getPostsEdited()
    {
        return $this->postsEdited;
    }

    /**
     * @param Collection|Topic[] $postsEdited
     */
    public function setPostsEdited($postsEdited)
    {
        $this->postsEdited = $postsEdited;
    }
}
