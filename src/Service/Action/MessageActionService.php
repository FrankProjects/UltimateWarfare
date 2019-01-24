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
    /**
     * @var PlayerRepository
     */
    private $playerRepository;

    /**
     * @var MessageRepository
     */
    private $messageRepository;

    /**
     * MessageActionService constructor.
     *
     * @param PlayerRepository $playerRepository
     * @param MessageRepository $messageRepository
     */
    public function __construct(
        PlayerRepository $playerRepository,
        MessageRepository $messageRepository
    ) {
        $this->playerRepository = $playerRepository;
        $this->messageRepository = $messageRepository;
    }

    /**
     * @param Player $player
     * @param int $messageId
     */
    public function deleteMessageFromInbox(Player $player, int $messageId)
    {
        $message = $this->getMessage($messageId);

        if ($message->getToPlayer()->getId() !== $player->getId()) {
            throw new RunTimeException('This is not your message!');
        }

        $message->setToDelete(true);
        $this->messageRepository->save($message);
    }

    /**
     * @param Player $player
     * @param int $messageId
     */
    public function deleteMessageFromOutbox(Player $player, int $messageId)
    {
        $message = $this->getMessage($messageId);

        if ($message->getFromPlayer()->getId() !== $player->getId()) {
            throw new RunTimeException('This is not your message!');
        }

        $message->setFromDelete(true);
        $this->messageRepository->save($message);
    }

    /**
     * @param Player $player
     * @param string $subject
     * @param string $message
     * @param string $toPlayerName
     * @param bool $adminMessage
     */
    public function sendMessage(Player $player, string $subject, string $message, string $toPlayerName, bool $adminMessage)
    {
        if ($subject == '') {
            throw new RunTimeException('Please type a subject');
        }

        if ($message == '') {
            throw new RunTimeException('Please type a message');
        }

        $toPlayer = $this->playerRepository->findByNameAndWorld($toPlayerName, $player->getWorld());

        if (!$toPlayer) {
            throw new RunTimeException('No such player');
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

    /**
     * @param int $messageId
     * @return Message
     */
    private function getMessage(int $messageId): Message
    {
        $message = $this->messageRepository->find($messageId);

        if (!$message) {
            throw new RunTimeException('No such message!');
        }

        return $message;
    }

    /**
     * @param int $messageId
     * @param Player $player
     * @return Message
     */
    public function getMessageByIdAndToPlayer(int $messageId, Player $player): Message
    {
        $message = $this->getMessage($messageId);

        if ($message->getToPlayer()->getId() !== $player->getId()) {
            throw new RunTimeException('This is not your message!');
        }

        return $message;
    }

    /**
     * @param int $messageId
     * @param Player $player
     * @return Message
     */
    public function getMessageByIdAndFromPlayer(int $messageId, Player $player): Message
    {
        $message = $this->getMessage($messageId);

        if ($message->getFromPlayer()->getId() !== $player->getId()) {
            throw new RunTimeException('This is not your message!');
        }

        return $message;
    }

    /**
     * @param Player $player
     */
    public function disableMessageNotification(Player $player): void
    {
        $player->getNotifications()->setMessage(false);
        $this->playerRepository->save($player);
    }
}
