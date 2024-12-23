<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository\Doctrine;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FrankProjects\UltimateWarfare\Entity\Category;
use FrankProjects\UltimateWarfare\Entity\Post;
use FrankProjects\UltimateWarfare\Entity\Topic;
use FrankProjects\UltimateWarfare\Entity\User;
use FrankProjects\UltimateWarfare\Repository\TopicRepository;

final class DoctrineTopicRepository implements TopicRepository
{
    private EntityManagerInterface $entityManager;

    /**
     * @var EntityRepository <Topic>
     */
    private EntityRepository $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $this->entityManager->getRepository(Topic::class);
    }

    public function find(int $id): ?Topic
    {
        return $this->repository->find($id);
    }

    public function getLastTopicByUser(User $user): ?Topic
    {
        return $this->entityManager->createQuery(
            'SELECT t FROM ' . Topic::class . ' t WHERE t.user = :user ORDER BY t.createDateTime DESC'
        )
            ->setParameter('user', $user->getId())
            ->setMaxResults(1)
            ->getOneOrNullResult(AbstractQuery::HYDRATE_OBJECT);
    }

    /**
     * @param Category $category
     * @return Topic[]
     */
    public function getByCategorySortedByStickyAndDate(Category $category): array
    {
        return $this->entityManager->createQuery(
            'SELECT t FROM ' . Topic::class . ' t
                 LEFT JOIN ' . Post::class . ' p WITH p.topic = t 
                 WHERE t.category = :category
                 ORDER BY t.sticky, p.createDateTime DESC'
        )
            ->setParameter('category', $category->getId())
            ->getResult();
    }

    public function remove(Topic $topic): void
    {
        $this->entityManager->remove($topic);
        $this->entityManager->flush();
    }

    public function save(Topic $topic): void
    {
        $this->entityManager->persist($topic);
        $this->entityManager->flush();
    }
}
