<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Tests\Service\Action;

use Doctrine\Common\Collections\ArrayCollection;
use FrankProjects\UltimateWarfare\Entity\Federation;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\Post;
use FrankProjects\UltimateWarfare\Entity\Topic;
use FrankProjects\UltimateWarfare\Entity\UnbanRequest;
use FrankProjects\UltimateWarfare\Entity\User;
use FrankProjects\UltimateWarfare\Entity\World;
use FrankProjects\UltimateWarfare\Repository\PlayerRepository;
use FrankProjects\UltimateWarfare\Repository\PostRepository;
use FrankProjects\UltimateWarfare\Repository\TopicRepository;
use FrankProjects\UltimateWarfare\Repository\UnbanRequestRepository;
use FrankProjects\UltimateWarfare\Repository\UserIpLogRepository;
use FrankProjects\UltimateWarfare\Repository\UserRepository;
use FrankProjects\UltimateWarfare\Service\Action\UserActionService;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class UserActionServiceTest extends TestCase
{
    /** @var string[] */
    private array $calls = [];

    public function testDeleteUserRemovesPlayersAndPersonalDataAndKeepsForumContent(): void
    {
        $user = new User();
        $player = $this->createPlayer($user, Player::FEDERATION_HIERARCHY_RECRUIT);
        $user->setPlayers(new ArrayCollection([$player]));

        $topic = new Topic();
        $topic->setUser($user);
        $topic->setEditUser($user);
        $user->setTopics(new ArrayCollection([$topic]));
        $user->setTopicsEdited(new ArrayCollection([$topic]));

        $post = new Post();
        $post->setUser($user);
        $post->setEditUser($user);
        $user->setPosts(new ArrayCollection([$post]));
        $user->setPostsEdited(new ArrayCollection([$post]));

        $this->createService(new UnbanRequest())->deleteUser($user);

        self::assertNull($topic->getUser());
        self::assertNull($topic->getEditUser());
        self::assertNull($post->getUser());
        self::assertNull($post->getEditUser());
        self::assertSame(
            [
                'removePlayer',
                'saveTopic',
                'saveTopic',
                'savePost',
                'savePost',
                'removeUnbanRequest',
                'removeIpLog',
                'removeUser',
            ],
            $this->calls
        );
    }

    public function testDeleteUserWithoutUnbanRequest(): void
    {
        $this->createService(null)->deleteUser(new User());

        self::assertSame(['removeIpLog', 'removeUser'], $this->calls);
    }

    public function testDeleteUserIsRefusedForFederationGeneral(): void
    {
        $user = new User();
        $user->setPlayers(new ArrayCollection([
            $this->createPlayer($user, Player::FEDERATION_HIERARCHY_RECRUIT),
            $this->createPlayer($user, Player::FEDERATION_HIERARCHY_GENERAL),
        ]));

        try {
            $this->createService(null)->deleteUser($user);
            self::fail('Expected RuntimeException');
        } catch (RuntimeException $e) {
            self::assertStringContainsString('Federation General in world Alpha', $e->getMessage());
        }

        self::assertSame([], $this->calls);
    }

    public function testDeleteUserIsRefusedForBannedUser(): void
    {
        $user = new User();
        $user->setBanned(true);

        try {
            $this->createService(null)->deleteUser($user);
            self::fail('Expected RuntimeException');
        } catch (RuntimeException $e) {
            self::assertSame('A banned account cannot be deleted.', $e->getMessage());
        }

        self::assertSame([], $this->calls);
    }

    public function testUnbanLiftsBanAndRemovesUnbanRequest(): void
    {
        $user = new User();
        $user->setBanned(true);

        $this->createService(new UnbanRequest())->unban($user);

        self::assertFalse($user->isBanned());
        self::assertSame(['saveUser', 'removeUnbanRequest'], $this->calls);
    }

    private function createPlayer(User $user, int $federationHierarchy): Player
    {
        $world = new World();
        $world->setName('Alpha');

        $player = new Player();
        $player->setUser($user);
        $player->setWorld($world);
        $player->setFederation(new Federation());
        $player->setFederationHierarchy($federationHierarchy);

        return $player;
    }

    private function createService(?UnbanRequest $unbanRequest): UserActionService
    {
        $userRepository = self::createStub(UserRepository::class);
        $userRepository->method('remove')->willReturnCallback(fn () => $this->calls[] = 'removeUser');
        $userRepository->method('save')->willReturnCallback(fn () => $this->calls[] = 'saveUser');

        $playerRepository = self::createStub(PlayerRepository::class);
        $playerRepository->method('remove')->willReturnCallback(fn () => $this->calls[] = 'removePlayer');

        $topicRepository = self::createStub(TopicRepository::class);
        $topicRepository->method('save')->willReturnCallback(fn () => $this->calls[] = 'saveTopic');

        $postRepository = self::createStub(PostRepository::class);
        $postRepository->method('save')->willReturnCallback(fn () => $this->calls[] = 'savePost');

        $unbanRequestRepository = self::createStub(UnbanRequestRepository::class);
        $unbanRequestRepository->method('findByUser')->willReturn($unbanRequest);
        $unbanRequestRepository->method('remove')->willReturnCallback(fn () => $this->calls[] = 'removeUnbanRequest');

        $userIpLogRepository = self::createStub(UserIpLogRepository::class);
        $userIpLogRepository->method('removeByUser')->willReturnCallback(fn () => $this->calls[] = 'removeIpLog');

        return new UserActionService(
            $userRepository,
            $playerRepository,
            $topicRepository,
            $postRepository,
            $unbanRequestRepository,
            $userIpLogRepository
        );
    }
}
