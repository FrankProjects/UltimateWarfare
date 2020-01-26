<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

use DateTime;
use Exception;

class Contact
{
    private ?int $id;
    private string $name = '';
    private string $email = '';
    private string $subject = '';
    private string $message = '';
    private DateTime $createDateTime;

    public function __construct()
    {
        try {
            $this->createDateTime = new DateTime();
        } catch (Exception $e) {
        }
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): void
    {
        $this->subject = $subject;
    }

    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getCreateDateTime(): DateTime
    {
        return $this->createDateTime;
    }

    public function setCreateDateTime(DateTime $createDateTime): void
    {
        $this->createDateTime = $createDateTime;
    }
}
