<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository\Doctrine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FrankProjects\UltimateWarfare\Entity\Post;
use FrankProjects\UltimateWarfare\Entity\User;
use FrankProjects\UltimateWarfare\Repository\PostRepository;

final class DoctrinePostRepository implements PostRepository
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var EntityRepository
     */
    private $repository;

    /**
     * DoctrinePostRepository constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $this->entityManager->getRepository(Post::class);
    }

    /**
     * @param int $id
     * @return Post|null
     */
    public function find(int $id): ?Post
    {
        return $this->repository->find($id);
    }

    /**
     * @param User $user
     * @return Post|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getLastPostByUser(User $user): ?Post
    {
        return $this->entityManager->createQuery(
                'SELECT p FROM Game:Post p WHERE p.user = :user ORDER BY p.createDateTime DESC'
            )
            ->setParameter('user', $user->getId())
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }

    /**
     * @param Post $post
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function remove(Post $post): void
    {
        $this->entityManager->remove($post);
        $this->entityManager->flush();
    }

    /**
     * @param Post $post
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function save(Post $post): void
    {
        $this->entityManager->persist($post);
        $this->entityManager->flush();
    }
}
