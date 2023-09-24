<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\Action;

use FrankProjects\UltimateWarfare\Entity\Message;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\User;
use FrankProjects\UltimateWarfare\Repository\MessageRepository;
use FrankProjects\UltimateWarfare\Repository\PlayerRepository;
use RuntimeException;

final class MessageActionService
{
    private PlayerRepository $playerRepository;
    private MessageRepository $messageRepository;

    public function __construct(
        PlayerRepository $playerRepository,
        MessageRepository $messageRepository
    ) {
        $this->playerRepository = $playerRepository;
        $this->messageRepository = $messageRepository;
    }

    public function deleteMessageFromInbox(Player $player, int $messageId): void
    {
        $message = $this->getMessage($messageId);

        if ($message->getToPlayer()->getId() !== $player->getId()) {
            throw new RuntimeException('This is not your message!');
        }

        $message->setToDelete(true);
        $this->messageRepository->save($message);
    }

    public function deleteMessageFromOutbox(Player $player, int $messageId): void
    {
        $message = $this->getMessage($messageId);

        if ($message->getFromPlayer()->getId() !== $player->getId()) {
            throw new RuntimeException('This is not your message!');
        }

        $message->setFromDelete(true);
        $this->messageRepository->save($message);
    }

    public function sendMessage(
        Player $player,
        string $subject,
        string $message,
        string $toPlayerName,
        bool $adminMessage
    ): void {
        if ($subject == '') {
            throw new RuntimeException('Please type a subject');
        }

        if ($message == '') {
            throw new RuntimeException('Please type a message');
        }

        $toPlayer = $this->playerRepository->findByNameAndWorld($toPlayerName, $player->getWorld());

        if ($toPlayer === null) {
            throw new RuntimeException('No such player');
        }

        if (!$player->getUser()->hasRole(User::ROLE_ADMIN)) {
            $adminMessage = false;
        }

        $message = Message::create($player, $toPlayer, $subject, $message, $adminMessage);
        $toPlayerNotifications = $toPlayer->getNotifications();
        $toPlayerNotifications->setMessage(true);
        $toPlayer->setNotifications($toPlayerNotifications);

        $this->messageRepository->save($message);
        $this->playerRepository->save($toPlayer);
    }

    private function getMessage(int $messageId): Message
    {
        $message = $this->messageRepository->find($messageId);

        if ($message === null) {
            throw new RuntimeException('No such message!');
        }

        return $message;
    }

    public function getMessageByIdAndToPlayer(int $messageId, Player $player): Message
    {
        $message = $this->getMessage($messageId);

        if ($message->getToPlayer()->getId() !== $player->getId()) {
            throw new RuntimeException('This is not your message!');
        }

        return $message;
    }

    public function getMessageByIdAndFromPlayer(int $messageId, Player $player): Message
    {
        $message = $this->getMessage($messageId);

        if ($message->getFromPlayer()->getId() !== $player->getId()) {
            throw new RuntimeException('This is not your message!');
        }

        return $message;
    }

    public function disableMessageNotification(Player $player): void
    {
        $player->getNotifications()->setMessage(false);
        $this->playerRepository->save($player);
    }
}
