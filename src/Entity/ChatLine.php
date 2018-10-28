<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

/**
 * ChatLine
 */
class ChatLine
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $text;

    /**
     * @var int
     */
    private $timestamp;

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
     * Set name
     *
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set text
     *
     * @param string $text
     */
    public function setText(string $text): void
    {
        $this->text = $text;
    }

    /**
     * Get text
     *
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * Set timestamp
     *
     * @param int $timestamp
     */
    public function setTimestamp(int $timestamp): void
    {
        $this->timestamp = $timestamp;
    }

    /**
     * Get timestamp
     *
     * @return int
     */
    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    /**
     * @param string $name
     * @param string $text
     * @param int $timestamp
     *
     * @return ChatLine
     */
    public static function create(string $name, string $text, int $timestamp): ChatLine
    {
        $chatLine = new ChatLine();
        $chatLine->setName($name);
        $chatLine->setText($text);
        $chatLine->setTimestamp($timestamp);

        return $chatLine;
    }
}
