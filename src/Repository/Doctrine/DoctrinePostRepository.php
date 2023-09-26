<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FrankProjects\UltimateWarfare\Entity\Post;
use FrankProjects\UltimateWarfare\Entity\User;
use FrankProjects\UltimateWarfare\Repository\PostRepository;

final class DoctrinePostRepository implements PostRepository
{
    private EntityManagerInterface $entityManager;

    /**
     * @var EntityRepository <Post>
     */
    private EntityRepository $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $this->entityManager->getRepository(Post::class);
    }

    public function find(int $id): ?Post
    {
        return $this->repository->find($id);
    }

    public function getLastPostByUser(User $user): ?Post
    {
        return $this->entityManager->createQuery(
            'SELECT p FROM ' . Post::class . ' p WHERE p.user = :user ORDER BY p.createDateTime DESC'
        )
            ->setParameter('user', $user->getId())
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }

    public function remove(Post $post): void
    {
        $this->entityManager->remove($post);
        $this->entityManager->flush();
    }

    public function save(Post $post): void
    {
        $this->entityManager->persist($post);
        $this->entityManager->flush();
    }
}
