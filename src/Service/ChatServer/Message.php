<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\ChatServer;

class Message
{
    private string $type;
    private string $name;
    private string $message;
    private int $timestamp;

    public function __construct(string $type, string $name, string $message)
    {
        $this->type = $type;
        $this->name = $name;
        $this->message = $message;
        $this->timestamp = time();
    }

    /**
     * @return array
     */
    public function getStruct(): array
    {
        return [
            'type' => $this->type,
            'name' => $this->name,
            'message' => $this->message,
            'timestamp' => $this->timestamp
        ];
    }
}