<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\ChatLine;

interface ChatLineRepository
{
    /**
     * @param int $chatLineId
     * @return ChatLine[]
     */
    public function findChatLinesByLastChatLineId(int $chatLineId): array;

    /**
     * @param int $seconds
     * @return ChatLine[]
     */
    public function findChatLinesOlderThanSeconds(int $seconds): array;

    public function remove(ChatLine $chatLine): void;

    public function save(ChatLine $chatLine): void;
}
