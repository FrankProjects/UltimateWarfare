<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

/**
 * GameNews
 */
class GameNews
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $message;

    /**
     * @var \DateTime
     */
    private $createDateTime;

    /**
     * @var bool
     */
    private $enabled;

    /**
     * @var bool
     */
    private $mainpage;


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
     * Set message
     *
     * @param string $message
     */
    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Set enabled
     *
     * @param bool $enabled
     */
    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    /**
     * Get enabled
     *
     * @return bool
     */
    public function getEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Set mainpage
     *
     * @param bool $mainpage
     */
    public function setMainpage(bool $mainpage): void
    {
        $this->mainpage = $mainpage;
    }

    /**
     * Get mainpage
     *
     * @return bool
     */
    public function getMainpage(): bool
    {
        return $this->mainpage;
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
}
