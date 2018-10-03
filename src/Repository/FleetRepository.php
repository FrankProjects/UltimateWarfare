<?php

namespace FrankProjects\UltimateWarfare\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FrankProjects\UltimateWarfare\Entity\Fleet;

class FleetRepository
{
    /**
     * @var EntityRepository
     */
    private $repository;

    /**
     * FleetRepository constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(Fleet::class);
    }

    /**
     * @param int $id
     * @return Fleet|null
     */
    public function find(int $id): ?Fleet
    {
        return $this->repository->find($id);
    }
}
