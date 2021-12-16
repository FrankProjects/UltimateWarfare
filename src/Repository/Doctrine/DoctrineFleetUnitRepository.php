<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use FrankProjects\UltimateWarfare\Entity\FleetUnit;
use FrankProjects\UltimateWarfare\Repository\FleetUnitRepository;

final class DoctrineFleetUnitRepository implements FleetUnitRepository
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function remove(FleetUnit $fleetUnit): void
    {
        $this->entityManager->remove($fleetUnit);
        $this->entityManager->flush();
    }

    public function save(FleetUnit $fleetUnit): void
    {
        $this->entityManager->persist($fleetUnit);
        $this->entityManager->flush();
    }
}
