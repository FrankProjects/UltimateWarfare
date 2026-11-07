<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service\Action;

use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\User;
use FrankProjects\UltimateWarfare\Repository\PlayerRepository;
use FrankProjects\UltimateWarfare\Repository\PostRepository;
use FrankProjects\UltimateWarfare\Repository\TopicRepository;
use FrankProjects\UltimateWarfare\Repository\UnbanRequestRepository;
use FrankProjects\UltimateWarfare\Repository\UserIpLogRepository;
use FrankProjects\UltimateWarfare\Repository\UserRepository;
use RuntimeException;

final class UserActionService
{
    private UserRepository $userRepository;
    private PlayerRepository $playerRepository;
    private TopicRepository $topicRepository;
    private PostRepository $postRepository;
    private UnbanRequestRepository $unbanRequestRepository;
    private UserIpLogRepository $userIpLogRepository;

    public function __construct(
        UserRepository $userRepository,
        PlayerRepository $playerRepository,
        TopicRepository $topicRepository,
        PostRepository $postRepository,
        UnbanRequestRepository $unbanRequestRepository,
        UserIpLogRepository $userIpLogRepository
    ) {
        $this->userRepository = $userRepository;
        $this->playerRepository = $playerRepository;
        $this->topicRepository = $topicRepository;
        $this->postRepository = $postRepository;
        $this->unbanRequestRepository = $unbanRequestRepository;
        $this->userIpLogRepository = $userIpLogRepository;
    }

    public function addRoleToUser(User $user, string $role): void
    {
        if ($user->hasRole($role)) {
            throw new RuntimeException('User already has this role');
        }

        $user->addRole($role);
        $this->userRepository->save($user);
    }

    public function removeRoleFromUser(User $user, string $role): void
    {
        if (!$user->hasRole($role)) {
            throw new RuntimeException('User does not have this role');
        }

        $user->removeRole($role);
        $this->userRepository->save($user);
    }

    public function unban(User $user): void
    {
        $user->setBanned(false);
        $this->userRepository->save($user);

        $unbanRequest = $this->unbanRequestRepository->findByUser($user);
        if ($unbanRequest !== null) {
            $this->unbanRequestRepository->remove($unbanRequest);
        }
    }

    /**
     * Permanently deletes the account and its personal data (GDPR right to erasure): all players (as on
     * surrender), the unban request and the IP log. Forum topics and posts are kept without author.
     * Banned users can't be deleted: their data is kept for multi-account detection; unban first to delete.
     */
    public function deleteUser(User $user): void
    {
        if ($user->isBanned()) {
            throw new RuntimeException('A banned account cannot be deleted.');
        }

        foreach ($user->getPlayers() as $player) {
            if (
                $player->getFederation() !== null
                && $player->getFederationHierarchy() === Player::FEDERATION_HIERARCHY_GENERAL
            ) {
                throw new RuntimeException(
                    "You cannot delete your account while you are a Federation General in world "
                    . "{$player->getWorld()->getName()}. Please hand over or disband your Federation first."
                );
            }
        }

        foreach ($user->getPlayers()->toArray() as $player) {
            $this->playerRepository->remove($player);
        }

        $this->removeForumAuthorship($user);

        $unbanRequest = $this->unbanRequestRepository->findByUser($user);
        if ($unbanRequest !== null) {
            $this->unbanRequestRepository->remove($unbanRequest);
        }

        $this->userIpLogRepository->removeByUser($user);
        $this->userRepository->remove($user);
    }

    private function removeForumAuthorship(User $user): void
    {
        foreach ($user->getTopics()->toArray() as $topic) {
            $topic->setUser(null);
            $this->topicRepository->save($topic);
        }

        foreach ($user->getTopicsEdited()->toArray() as $topic) {
            $topic->setEditUser(null);
            $this->topicRepository->save($topic);
        }

        foreach ($user->getPosts()->toArray() as $post) {
            $post->setUser(null);
            $this->postRepository->save($post);
        }

        foreach ($user->getPostsEdited()->toArray() as $post) {
            $post->setEditUser(null);
            $this->postRepository->save($post);
        }
    }
}
