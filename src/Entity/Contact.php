<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

/**
 * Contact
 */
class Contact
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name = '';

    /**
     * @var string
     */
    private $email = '';

    /**
     * @var string
     */
    private $subject = '';

    /**
     * @var string
     */
    private $message = '';

    /**
     * @var \DateTime
     */
    private $createDateTime;

    /**
     * Contact constructor.
     */
    public function __construct()
    {
        try {
            $this->createDateTime = new \DateTime();
        } catch (\Exception $e) {
        }
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
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     */
    public function setSubject(string $subject): void
    {
        $this->subject = $subject;
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
