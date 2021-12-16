<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Serializable;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class User implements UserInterface, PasswordAuthenticatedUserInterface, EquatableInterface, Serializable
{
    private const ROLE_DEFAULT = 'ROLE_USER';
    public const ROLE_ADMIN = 'ROLE_ADMIN';

    private ?int $id;
    private string $username = '';
    private string $email = '';
    private bool $enabled = false;
    private string $password = '';
    private string $plainPassword = '';
    private ?DateTime $lastLogin;
    /** Random string sent to the user email address in order to verify it */
    private ?string $confirmationToken = null;
    private ?DateTime $passwordRequestedAt = null;
    private array $roles;
    private DateTime $signup;
    private bool $active = true;
    private bool $adviser = false;
    private bool $forumBan = false;

    /** @var Collection<Player> */
    private Collection $players;

    /** @var Collection<Topic> */
    private Collection $topics;

    /** @var Collection<Topic> */
    private Collection $topicsEdited;

    /** @var Collection<Topic> */
    private Collection $topicsLastPost;

    /** @var Collection<Topic> */
    private Collection $posts;

    /** @var Collection<Topic> */
    private Collection $postsEdited;

    public function __construct()
    {
        $this->roles = [];
        $this->players = new ArrayCollection();
        $this->topics = new ArrayCollection();
        $this->topicsEdited = new ArrayCollection();
        $this->topicsLastPost = new ArrayCollection();
        $this->posts = new ArrayCollection();
        $this->postsEdited = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = User::ROLE_DEFAULT;

        return array_unique($roles);
    }

    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    public function hasRole(string $role): bool
    {
        return in_array(strtoupper($role), $this->getRoles(), true);
    }

    public function addRole(string $role): void
    {
        $role = strtoupper($role);
        if ($role === User::ROLE_DEFAULT) {
            return;
        }

        if (!in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }

        return;
    }

    public function removeRole(string $role): void
    {
        $key = array_search(strtoupper($role), $this->roles, true);
        if ($key !== false) {
            unset($this->roles[$key]);
        }
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function getPlainPassword(): string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(string $plainPassword): void
    {
        $this->plainPassword = $plainPassword;
    }

    /**
     * @return null
     */
    public function getSalt()
    {
        return null;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function eraseCredentials(): void
    {
        $this->plainPassword = '';
    }

    public function isEqualTo(UserInterface $user): bool
    {
        if (!$user instanceof User) {
            return false;
        }

        if ($this->password !== $user->getPassword()) {
            return false;
        }

        if ($this->username !== $user->getUsername()) {
            return false;
        }

        return true;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getLastLogin(): ?DateTime
    {
        return $this->lastLogin;
    }

    public function setLastLogin(DateTime $lastLogin): void
    {
        $this->lastLogin = $lastLogin;
    }

    public function getConfirmationToken(): ?string
    {
        return $this->confirmationToken;
    }

    public function setConfirmationToken(?string $confirmationToken): void
    {
        $this->confirmationToken = $confirmationToken;
    }

    public function getPasswordRequestedAt(): ?DateTime
    {
        return $this->passwordRequestedAt;
    }

    public function setPasswordRequestedAt(DateTime $passwordRequestedAt): void
    {
        $this->passwordRequestedAt = $passwordRequestedAt;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return serialize(
            [
                $this->id,
                $this->username,
                $this->enabled,
                $this->email,
                $this->password
            ]
        );
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        list(
            $this->id,
            $this->username,
            $this->enabled,
            $this->email,
            $this->password
            ) = unserialize($serialized);
    }

    public function setSignup(DateTime $signup): void
    {
        $this->signup = $signup;
    }

    public function getSignup(): DateTime
    {
        return $this->signup;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function getActive(): bool
    {
        return $this->active;
    }

    public function setAdviser(bool $adviser): void
    {
        $this->adviser = $adviser;
    }

    public function getAdviser(): bool
    {
        return $this->adviser;
    }

    public function setForumBan(bool $forumBan): void
    {
        $this->forumBan = $forumBan;
    }

    public function getForumBan(): bool
    {
        return $this->forumBan;
    }

    public function getPlayers(): Collection
    {
        return $this->players;
    }

    public function setPlayers(Collection $players): void
    {
        $this->players = $players;
    }

    public function getTopics(): Collection
    {
        return $this->topics;
    }

    public function setTopics(Collection $topics): void
    {
        $this->topics = $topics;
    }

    public function getTopicsEdited(): Collection
    {
        return $this->topicsEdited;
    }

    public function setTopicsEdited(Collection $topicsEdited): void
    {
        $this->topicsEdited = $topicsEdited;
    }

    public function getTopicsLastPost(): Collection
    {
        return $this->topicsLastPost;
    }

    public function setTopicsLastPost(Collection $topicsLastPost): void
    {
        $this->topicsLastPost = $topicsLastPost;
    }

    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function setPosts(Collection $posts): void
    {
        $this->posts = $posts;
    }

    public function getPostsEdited(): Collection
    {
        return $this->postsEdited;
    }

    public function setPostsEdited(Collection $postsEdited): void
    {
        $this->postsEdited = $postsEdited;
    }

    public function getUserIdentifier(): string
    {
        return $this->username;
    }
}
