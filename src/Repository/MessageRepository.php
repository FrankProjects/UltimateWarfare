<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\Message;
use FrankProjects\UltimateWarfare\Entity\Player;

interface MessageRepository
{
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

    /**
     * @param Message $message
     */
    public function remove(Message $message): void;

    /**
     * @param Message $message
     */
    public function save(Message $message): void;
}
