<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\ChatServer;

use Exception;
use Psr\Log\LoggerInterface;
use SplObjectStorage;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

use function json_encode;

class ConnectionHandler implements MessageComponentInterface
{
    protected SplObjectStorage $connections;
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->connections = new SplObjectStorage();
    }

    /**
     * @param ConnectionInterface $conn
     */
    public function onOpen(ConnectionInterface $conn): void
    {
        $this->logger->debug('new connection!');
        $this->connections->attach($conn);
        $message = new Message('message', 'System', 'Welcome to chat!');
        $conn->send(json_encode($message->getStruct()));
    }

    /**
     * @param ConnectionInterface $conn
     */
    public function onClose(ConnectionInterface $conn): void
    {
        $this->connections->detach($conn);
    }

    /**
     * @param ConnectionInterface $conn
     * @param Exception $e
     */
    public function onError(ConnectionInterface $conn, Exception $e): void
    {
        $this->logger->error($e->getMessage());
        $conn->close();
    }

    /**
     * @param ConnectionInterface $from
     * @param string $msg
     */
    public function onMessage(ConnectionInterface $from, $msg): void
    {
        $this->logger->debug('new message!');
        $message = Message::parseMessage($msg);
        if ($message === null) {
            $this->logger->debug('faulty message=' . $msg);
            return;
        }

        foreach ($this->connections as $connection) {
            switch ($message->getType()) {
                case Message::TYPE_MESSAGE:
                    $this->logger->debug('onMessage message type');

                    if ($from != $connection) {
                        $this->logger->debug('send message to client...');
                        $connection->send(json_encode($message->getStruct()));
                    }
                    break;
                default:
                    $this->logger->warning("onMessage no valid type type {$message->getType()}");
            }
        }
    }
}
