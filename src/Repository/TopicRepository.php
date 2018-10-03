<?php

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\Category;
use FrankProjects\UltimateWarfare\Entity\Topic;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class TopicRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Topic::class);
    }

    /**
     * @param int $limit
     * @return array
     */
    public function findLastAnnouncements(int $limit): array
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT t.id, t.title FROM Game:Topic t WHERE t.category = :category ORDER BY t.createDateTime DESC'
            )
            ->setParameter('category', 1)
            ->setMaxResults(intval($limit))
            ->getResult();
    }

    /**
     * @param Category $category
     * @return array
     */
    public function getByCategorySortedByStickyAndDate(Category $category): array
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT t FROM Game:Topic t
                 LEFT JOIN Game:Post p WITH p.topic = t 
                 WHERE t.category = :category
                 ORDER BY t.sticky, p.createDateTime DESC'
            )
            ->setParameter('category', $category->getId())
            ->getResult();
    }
}
