<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

use Doctrine\Common\Collections\Collection;

class Category
{
    private int $id;
    private string $title;

    /**
     * @var Collection<int, Topic>
     */
    private Collection $topics;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return Collection<int, Topic>
     */
    public function getTopics(): Collection
    {
        return $this->topics;
    }

    /**
     * @param Collection<int, Topic> $topics
     */
    public function setTopics(Collection $topics): void
    {
        $this->topics = $topics;
    }
}
