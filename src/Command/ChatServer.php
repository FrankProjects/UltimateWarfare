<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Command;

use FrankProjects\UltimateWarfare\Service\ChatServer\ConnectionHandler;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ChatServer extends Command
{
    protected function configure(): void
    {
        $this->setName('chat:start')
            ->setDescription('Starts chat server');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $port = 8080;
        $address = '0.0.0.0';

        $output->writeln(
            [
                "Starting chat server on {$address} port {$port}",
                '============',
            ]
        );

        $chatServer = IoServer::factory(
            new HttpServer(
                new WsServer(
                    new ConnectionHandler()
                )
            ),
            $port,
            $address
        );

        $chatServer->run();

        return Command::SUCCESS;
    }
}
