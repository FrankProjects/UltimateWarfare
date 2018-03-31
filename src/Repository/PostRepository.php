<?php

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\User;
use FrankProjects\UltimateWarfare\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class PostRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Post::class);
    }

    /**
     * @param User $user
     * @return Post|null
     */
    public function getLastPostByUser(User $user)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT p FROM Game:Post p WHERE p.user = :user ORDER BY p.createDateTime DESC'
            )
            ->setParameter('user', $user->getId())
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }
}

