<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

/**
 * ChatUser
 */
class ChatUser
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
     * @var int
     */
    private $timestampActivity;

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
     * Set timestampActivity
     *
     * @param int $timestampActivity
     */
    public function setTimestampActivity(int $timestampActivity): void
    {
        $this->timestampActivity = $timestampActivity;
    }

    /**
     * Get timestampActivity
     *
     * @return int
     */
    public function getTimestampActivity(): int
    {
        return $this->timestampActivity;
    }

    /**
     * @param string $name
     * @param int $timestampActivity
     * @return ChatUser
     */
    public static function create(string $name, int $timestampActivity): ChatUser
    {
        $chatUser = new ChatUser();
        $chatUser->setName($name);
        $chatUser->setTimestampActivity($timestampActivity);

        return $chatUser;
    }
}
