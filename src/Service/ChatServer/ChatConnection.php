<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\ChatServer;

use Ratchet\ConnectionInterface;
use Ratchet\WebSocket\WsConnection;

class ChatConnection extends WsConnection implements ConnectionInterface
{
    private ChatUser $chatUser;
    public function __construct()
    {
        $this->chatUser = new ChatUser();
        parent::__construct($this);
    }

    public function getChatUserName(): string
    {
        return $this->chatUser->getName();
    }
}