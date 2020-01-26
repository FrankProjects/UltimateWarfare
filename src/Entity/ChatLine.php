<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

class ChatLine
{
    private ?int $id;
    private string $name;
    private string $text;
    private int $timestamp;

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

    public function setText(string $text): void
    {
        $this->text = $text;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setTimestamp(int $timestamp): void
    {
        $this->timestamp = $timestamp;
    }

    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    public static function create(string $name, string $text, int $timestamp): ChatLine
    {
        $chatLine = new ChatLine();
        $chatLine->setName($name);
        $chatLine->setText($text);
        $chatLine->setTimestamp($timestamp);

        return $chatLine;
    }
}
