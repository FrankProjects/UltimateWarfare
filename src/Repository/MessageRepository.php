<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\Message;
use FrankProjects\UltimateWarfare\Entity\Player;

interface MessageRepository
{
    public function find(int $id): ?Message;

    /**
     * @param Player $player
     * @param int $limit
     * @return Message[]
     */
    public function findNonDeletedMessagesToPlayer(Player $player, int $limit = 100): array;

    /**
     * @param Player $player
     * @param int $limit
     * @return Message[]
     */
    public function findNonDeletedMessagesFromPlayer(Player $player, int $limit = 100): array;

    public function remove(Message $message): void;

    public function save(Message $message): void;
}
