<?php

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
     * @var /DateTime
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
     * @var GameAccount
     */
    private $gameAccount;

    /**
     * @var GameAccount|null
     */
    private $editGameAccount;

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
    public function setTitle(string $title)
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
    public function setPosterIp($posterIp)
    {
        $this->posterIp = $posterIp;
    }

    /**
     * Get posterIp
     *
     * @return string
     */
    public function getPosterIp()
    {
        return $this->posterIp;
    }

    /**
     * Set sticky
     *
     * @param bool $sticky
     */
    public function setSticky($sticky)
    {
        $this->sticky = $sticky;
    }

    /**
     * Get sticky
     *
     * @return bool
     */
    public function getSticky()
    {
        return $this->sticky;
    }

    /**
     * Set closed
     *
     * @param bool $closed
     */
    public function setClosed($closed)
    {
        $this->closed = $closed;
    }

    /**
     * Get closed
     *
     * @return bool
     */
    public function getClosed()
    {
        return $this->closed;
    }

    /**
     * Set content
     *
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
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
    public function setCategory(Category $category)
    {
        $this->category = $category;
    }

    /**
     * @return GameAccount
     */
    public function getGameAccount(): GameAccount
    {
        return $this->gameAccount;
    }

    /**
     * @param GameAccount $gameAccount
     */
    public function setGameAccount(GameAccount $gameAccount)
    {
        $this->gameAccount = $gameAccount;
    }

    /**
     * @return GameAccount|null
     */
    public function getEditGameAccount()
    {
        return $this->editGameAccount;
    }

    /**
     * @param GameAccount $editGameAccount
     */
    public function setEditGameAccount(GameAccount $editGameAccount)
    {
        $this->editGameAccount = $editGameAccount;
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
    public function setCreateDateTime(\DateTime $createDateTime)
    {
        $this->createDateTime = $createDateTime;
    }

    /**
     * @return Collection|Post[]
     */
    public function getPosts()
    {
        return $this->posts;
    }

    /**
     * @param Collection|Post[] $posts
     */
    public function setPosts(Collection $posts)
    {
        $this->posts = $posts;
    }
}
