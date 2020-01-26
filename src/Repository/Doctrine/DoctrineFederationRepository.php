<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FrankProjects\UltimateWarfare\Entity\Federation;
use FrankProjects\UltimateWarfare\Entity\World;
use FrankProjects\UltimateWarfare\Repository\FederationRepository;

final class DoctrineFederationRepository implements FederationRepository
{
    private EntityManagerInterface $entityManager;
    private EntityRepository $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $this->entityManager->getRepository(Federation::class);
    }

    public function findByIdAndWorld(int $id, World $world): ?Federation
    {
        return $this->repository->findOneBy(['id' => $id, 'world' => $world]);
    }

    public function findByNameAndWorld(string $name, World $world): ?Federation
    {
        return $this->repository->findOneBy(['name' => $name, 'world' => $world]);
    }

    /**
     * @param World $world
     * @return Federation[]
     */
    public function findByWorldSortedByRegion(World $world): array
    {
        return $this->repository->findBy(['world' => $world], ['regions' => 'DESC']);
    }

    public function remove(Federation $federation): void
    {
        foreach ($federation->getFederationApplications() as $application) {
            $this->entityManager->remove($application);
        }

        foreach ($federation->getFederationNews() as $news) {
            $this->entityManager->remove($news);
        }

        foreach ($federation->getPlayers() as $player) {
            $player->setFederationHierarchy(0);
            $player->setFederation(null);
            $this->entityManager->persist($player);
        }

        $this->entityManager->remove($federation);
        $this->entityManager->flush();
    }

    public function save(Federation $federation): void
    {
        $this->entityManager->persist($federation);
        $this->entityManager->flush();
    }
}
