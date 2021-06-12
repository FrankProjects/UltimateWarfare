<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\ChatServer;

class Message
{
    public const TYPE_MESSAGE = 'message';
    public const TYPE_AUTHENTICATE = 'authenticate';

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

    public function getStruct(): array
    {
        return [
            'type' => $this->getType(),
            'name' => $this->getName(),
            'message' => $this->getMessage(),
            'timestamp' => $this->getTimestamp()
        ];
    }

    public static function parseMessage(string $message): ?Message
    {
        $package = json_decode($message);

        if (is_object($package) == true) {
            if (
                property_exists($package, 'type') &&
                property_exists($package, 'message') &&
                self::isValidType($package->type) === true
            ) {
                // XXX TODO: Fix name for logged in users
                return new Message($package->type, '', $package->message);
            }
        }

        return null;
    }

    public static function isValidType(string $type): bool
    {
        return in_array($type, self::getAllTypes(), true);
    }

    public static function getAllTypes(): array
    {
        return [
            self::TYPE_MESSAGE,
            self::TYPE_AUTHENTICATE
        ];
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getTimestamp(): int
    {
        return $this->timestamp;
    }
}
