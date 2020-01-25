<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\ChatUser;

interface ChatUserRepository
{
    public function findByName(string $name): ?ChatUser;

    /**
     * @param int $limit
     * @return ChatUser[]
     */
    public function findWithLimit(int $limit): array;

    /**
     * @return ChatUser[]
     */
    public function findInactiveChatUsers(): array;

    public function remove(ChatUser $chatUser): void;

    public function save(ChatUser $chatUser): void;
}
