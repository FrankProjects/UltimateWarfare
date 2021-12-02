<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Command;

use FrankProjects\UltimateWarfare\Service\ChatServer\ConnectionHandler;
use Psr\Log\LoggerInterface;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

final class ChatServer extends Command
{
    protected function configure(): void
    {
        $this->setName('chat:start')
            ->setDescription('Starts chat server');
    }

    private ParameterBagInterface $parameterBag;
    private LoggerInterface $logger;

    public function __construct(ParameterBagInterface $parameterBag, LoggerInterface $logger)
    {
        $this->parameterBag = $parameterBag;
        $this->logger = $logger;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $port = $this->parameterBag->get('app.uw_chat_service_port');
        $address = $this->parameterBag->get('app.uw_chat_address');

        $output->writeln(
            [
                "Starting chat server on {$address}:{$port}",
                '============',
            ]
        );

        $chatServer = IoServer::factory(
            new HttpServer(
                new WsServer(
                    new ConnectionHandler($this->logger)
                )
            ),
            $port,
            $address
        );

        $chatServer->run();

        return Command::SUCCESS;
    }
}
