<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class User implements UserInterface, EquatableInterface, \Serializable
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $username = '';

    /**
     * @var string
     */
    private $email = '';

    /**
     * @var bool
     */
    private $enabled = false;

    /**
     * @var string
     */
    private $password = '';

    /**
     * @var string
     */
    private $plainPassword = '';

    /**
     * @var \DateTime
     */
    private $lastLogin;

    /**
     * Random string sent to the user email address in order to verify it.
     *
     * @var ?string
     */
    private $confirmationToken = null;

    /**
     * @var \DateTime
     */
    private $passwordRequestedAt = null;

    /**
     * @var array
     */
    private $roles;

    /**
     * @var \DateTime
     */
    private $signup;

    /**
     * @var bool
     */
    private $active = true;

    /**
     * @var bool
     */
    private $adviser = false;

    /**
     * @var bool
     */
    private $forumBan = false;

    /**
     * @var MapDesign
     */
    private $mapDesign;

    /**
     * @var Collection|Player[]
     */
    private $players = [];

    /**
     * @var Collection|Topic[]
     */
    private $topics = [];

    /**
     * @var Collection|Topic[]
     */
    private $topicsEdited = [];

    /**
     * @var Collection|Topic[]
     */
    private $topicsLastPost = [];

    /**
     * @var Collection|Topic[]
     */
    private $posts = [];

    /**
     * @var Collection|Topic[]
     */
    private $postsEdited = [];

    /**
     * User constructor.
     */
    public function __construct()
    {
        $this->roles = [];
        $this->enabled = false;
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
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param array $roles
     */
    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    /**
     * @param string $role
     * @return bool
     */
    public function hasRole(string $role): bool
    {
        return in_array(strtoupper($role), $this->getRoles(), true);
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getPlainPassword(): string
    {
        return $this->plainPassword;
    }

    /**
     * @param string $plainPassword
     */
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

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function eraseCredentials()
    {
        $this->plainPassword = '';
    }

    /**
     * @param UserInterface $user
     * @return bool
     */
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

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return \DateTime|null
     */
    public function getLastLogin(): ?\DateTime
    {
        return $this->lastLogin;
    }

    /**
     * @param \DateTime $lastLogin
     */
    public function setLastLogin(\DateTime $lastLogin): void
    {
        $this->lastLogin = $lastLogin;
    }

    /**
     * @return string|null
     */
    public function getConfirmationToken(): ?string
    {
        return $this->confirmationToken;
    }

    /**
     * @param string|null $confirmationToken
     */
    public function setConfirmationToken(?string $confirmationToken): void
    {
        $this->confirmationToken = $confirmationToken;
    }

    /**
     * @return \DateTime|null
     */
    public function getPasswordRequestedAt(): ?\DateTime
    {
        return $this->passwordRequestedAt;
    }

    /**
     * @param \DateTime $passwordRequestedAt
     */
    public function setPasswordRequestedAt(\DateTime $passwordRequestedAt): void
    {
        $this->passwordRequestedAt = $passwordRequestedAt;
    }

    /**
     * @param bool $enabled
     */
    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return serialize([
            $this->id,
            $this->username,
            $this->enabled,
            $this->email,
            $this->password
        ]);
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

    /**
     * Set signup
     *
     * @param \DateTime $signup
     */
    public function setSignup(\DateTime $signup): void
    {
        $this->signup = $signup;
    }

    /**
     * Get signup
     *
     * @return \DateTime
     */
    public function getSignup(): \DateTime
    {
        return $this->signup;
    }

    /**
     * Set active
     *
     * @param bool $active
     */
    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    /**
     * Get active
     *
     * @return bool
     */
    public function getActive(): bool
    {
        return $this->active;
    }

    /**
     * Set adviser
     *
     * @param bool $adviser
     */
    public function setAdviser(bool $adviser): void
    {
        $this->adviser = $adviser;
    }

    /**
     * Get adviser
     *
     * @return bool
     */
    public function getAdviser(): bool
    {
        return $this->adviser;
    }

    /**
     * Set forumBan
     *
     * @param bool $forumBan
     */
    public function setForumBan(bool $forumBan): void
    {
        $this->forumBan = $forumBan;
    }

    /**
     * Get forumBan
     *
     * @return bool
     */
    public function getForumBan(): bool
    {
        return $this->forumBan;
    }

    /**
     * @return Collection
     */
    public function getPlayers(): Collection
    {
        return $this->players;
    }

    /**
     * @param Collection $players
     */
    public function setPlayers(Collection $players): void
    {
        $this->players = $players;
    }

    /**
     * @return MapDesign
     */
    public function getMapDesign(): MapDesign
    {
        return $this->mapDesign;
    }

    /**
     * @param MapDesign $mapDesign
     */
    public function setMapDesign(MapDesign $mapDesign): void
    {
        $this->mapDesign = $mapDesign;
    }

    /**
     * @return Collection
     */
    public function getTopics(): Collection
    {
        return $this->topics;
    }

    /**
     * @param Collection $topics
     */
    public function setTopics(Collection $topics): void
    {
        $this->topics = $topics;
    }

    /**
     * @return Collection
     */
    public function getTopicsEdited(): Collection
    {
        return $this->topicsEdited;
    }

    /**
     * @param Collection $topicsEdited
     */
    public function setTopicsEdited(Collection $topicsEdited): void
    {
        $this->topicsEdited = $topicsEdited;
    }

    /**
     * @return Collection
     */
    public function getTopicsLastPost(): Collection
    {
        return $this->topicsLastPost;
    }

    /**
     * @param Collection $topicsLastPost
     */
    public function setTopicsLastPost(Collection $topicsLastPost): void
    {
        $this->topicsLastPost = $topicsLastPost;
    }

    /**
     * @return Collection
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    /**
     * @param Collection $posts
     */
    public function setPosts(Collection $posts): void
    {
        $this->posts = $posts;
    }

    /**
     * @return Collection
     */
    public function getPostsEdited(): Collection
    {
        return $this->postsEdited;
    }

    /**
     * @param Collection $postsEdited
     */
    public function setPostsEdited(Collection $postsEdited): void
    {
        $this->postsEdited = $postsEdited;
    }
}
