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
     * @param int $offset
     * @return Message[]
     */
    public function findNonDeletedMessagesToPlayer(Player $player, int $limit = 100, int $offset = 0): array;

    public function countNonDeletedMessagesToPlayer(Player $player): int;

    /**
     * @param Player $player
     * @param int $limit
     * @param int $offset
     * @return Message[]
     */
    public function findNonDeletedMessagesFromPlayer(Player $player, int $limit = 100, int $offset = 0): array;

    public function countNonDeletedMessagesFromPlayer(Player $player): int;

    public function remove(Message $message): void;

    public function save(Message $message): void;
}
