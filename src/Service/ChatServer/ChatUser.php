<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\ChatServer;

class ChatUser
{
    private string $name;

    public function __construct()
    {
        $this->name = uniqid('Guest_');
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}