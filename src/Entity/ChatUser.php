<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

class ChatUser
{
    private ?int $id;
    private string $name;
    private int $timestampActivity;

    public function getId(): int
    {
        return $this->id;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setTimestampActivity(int $timestampActivity): void
    {
        $this->timestampActivity = $timestampActivity;
    }

    public function getTimestampActivity(): int
    {
        return $this->timestampActivity;
    }

    public static function create(string $name, int $timestampActivity): ChatUser
    {
        $chatUser = new ChatUser();
        $chatUser->setName($name);
        $chatUser->setTimestampActivity($timestampActivity);

        return $chatUser;
    }
}
