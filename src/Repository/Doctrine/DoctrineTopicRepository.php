<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository\Doctrine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FrankProjects\UltimateWarfare\Entity\Category;
use FrankProjects\UltimateWarfare\Entity\Topic;
use FrankProjects\UltimateWarfare\Repository\TopicRepository;

final class DoctrineTopicRepository implements TopicRepository
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
     * DoctrineTopicRepository constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $this->entityManager->getRepository(Topic::class);
    }

    /**
     * @param int $id
     * @return Topic|null
     */
    public function find(int $id): ?Topic
    {
        return $this->repository->find($id);
    }

    /**
     * @param int $limit
     * @return Topic[]
     */
    public function findLastAnnouncements(int $limit): array
    {
        return $this->entityManager->createQuery(
                'SELECT t.id, t.title FROM Game:Topic t WHERE t.category = :category ORDER BY t.createDateTime DESC'
            )
            ->setParameter('category', 1)
            ->setMaxResults(intval($limit))
            ->getResult();
    }

    /**
     * @param Category $category
     * @return Topic[]
     */
    public function getByCategorySortedByStickyAndDate(Category $category): array
    {
        return $this->entityManager->createQuery(
                'SELECT t FROM Game:Topic t
                 LEFT JOIN Game:Post p WITH p.topic = t 
                 WHERE t.category = :category
                 ORDER BY t.sticky, p.createDateTime DESC'
            )
            ->setParameter('category', $category->getId())
            ->getResult();
    }

    /**
     * @param Topic $topic
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function remove(Topic $topic): void
    {
        $this->entityManager->remove($topic);
        $this->entityManager->flush();
    }

    /**
     * @param Topic $topic
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function save(Topic $topic): void
    {
        $this->entityManager->persist($topic);
        $this->entityManager->flush();
    }
}
