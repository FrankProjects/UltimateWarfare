<?php

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\GameAccount;
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
     * @param GameAccount $gameAccount
     * @return Post|null
     */
    public function getLastPostByGameAccount(GameAccount $gameAccount)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT p FROM Game:Post p WHERE p.gameAccount = :gameAccount ORDER BY p.createDateTime DESC'
            )
            ->setParameter('gameAccount', $gameAccount->getId())
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }
}

