<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FrankProjects\UltimateWarfare\Entity\Operation;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Repository\OperationRepository;

final class DoctrineOperationRepository implements OperationRepository
{
    private EntityManagerInterface $entityManager;
    private EntityRepository $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $this->entityManager->getRepository(Operation::class);
    }

    public function find(int $id): ?Operation
    {
        return $this->repository->find($id);
    }

    /**
     * @return Operation[]
     */
    public function findAll(): array
    {
        return $this->repository->findAll();
    }

    /**
     * @return Operation[]
     */
    public function findEnabled(): array
    {
        return $this->entityManager->createQuery(
            'SELECT o FROM Game:Operation o WHERE o.enabled = 1'
        )
            ->getResult();
    }

    public function findAvailableForPlayer(Player $player): array
    {
        return $this->entityManager->createQuery(
            'SELECT o
            FROM Game:Operation o 
            JOIN Game:Research r WITH o.research = r
            JOIN Game:ResearchPlayer rp WITH r = rp.research
            WHERE o.enabled = 1 AND rp.player = :player AND rp.active = 1'
        )
            ->setParameter('player', $player)
            ->getArrayResult();

        //JOIN Game:ResearchPlayer rp WITH r = rp.research
        //AND rp.player = :player AND rp.active = 1
        //->setParameter('player', $player)
        $operations = [];
        foreach ($results as $result) {
            $operations[$result['id']] = $result['name'];
        }

        return $operations;
        // "SELECT o.id, o.name FROM operations o LEFT JOIN research_player rp ON o.needs = rp.research_id WHERE (o.race_id = ".$player['race_id']." OR o.race_id = 0) AND o.active = 1 AND rp.player_id = $player_id");
    }

    public function remove(Operation $operation): void
    {
        $this->entityManager->remove($operation);
        $this->entityManager->flush();
    }

    public function save(Operation $operation): void
    {
        $this->entityManager->persist($operation);
        $this->entityManager->flush();
    }
}
