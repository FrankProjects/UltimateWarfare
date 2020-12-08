<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\ChatServer;

use Exception;
use SplObjectStorage;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

class ConnectionHandler implements MessageComponentInterface
{
    private array $chatConnections = [];
    protected ?SplObjectStorage $connections = null;

    public function __construct()
    {
        $this->connections = new SplObjectStorage();
    }

    /**
     * @param ConnectionInterface $conn
     */
    public function onOpen(ConnectionInterface $conn): void
    {
        echo 'new connection!' . PHP_EOL;
        $this->connections->attach($conn);
//$conn->resourceId;
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
        //$conn->send('Error ' . $e->getMessage() . PHP_EOL);
        $conn->close();
    }

    /**
     * @param ConnectionInterface $from
     * @param string $msg
     */
    public function onMessage(ConnectionInterface $from, $msg): void
    {
        echo 'new message!' . PHP_EOL;
        foreach ($this->connections as $connection) {
            $package = json_decode($msg);

            if (is_object($package) == true) {
                /**
                 * @var ChatConnection $from
                 */

                //$package->name = $from->getChatUserName();
                switch ($package->type) {
                    case 'message':
                        echo "onMessage message type" . PHP_EOL;

                        if ($from != $connection) {
                            echo "send message to client..." . PHP_EOL;
                            $connection->send(json_encode($package));
                        }
                        break;
                    default:
                        echo "onMessage no valid type type {$package->type}" . PHP_EOL;
                }
            }
        }
    }
}
