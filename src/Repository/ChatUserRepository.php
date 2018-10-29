<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\ChatUser;

interface ChatUserRepository
{
    /**
     * @param string $name
     * @return ChatUser|null
     */
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

    /**
     * @param ChatUser $chatUser
     */
    public function remove(ChatUser $chatUser): void;

    /**
     * @param ChatUser $chatUser
     */
    public function save(ChatUser $chatUser): void;
}
