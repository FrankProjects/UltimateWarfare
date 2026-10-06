<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Tests\Repository\Doctrine;

use Doctrine\Common\Collections\AbstractLazyCollection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use FrankProjects\UltimateWarfare\Entity\Construction;
use FrankProjects\UltimateWarfare\Entity\Enum\GameUnitEnum;
use FrankProjects\UltimateWarfare\Entity\Player;
use FrankProjects\UltimateWarfare\Entity\WorldRegion;
use FrankProjects\UltimateWarfare\Entity\WorldRegionLeveledUnit;
use FrankProjects\UltimateWarfare\Entity\WorldRegionStackableUnit;
use FrankProjects\UltimateWarfare\Repository\Doctrine\DoctrineConstructionRepository;
use FrankProjects\UltimateWarfare\Repository\Doctrine\DoctrineWorldRegionLeveledUnitRepository;
use FrankProjects\UltimateWarfare\Repository\Doctrine\DoctrineWorldRegionStackableUnitRepository;
use FrankProjects\UltimateWarfare\Repository\GameUnitRegistry;
use PHPUnit\Framework\TestCase;

class DoctrineRepositoryCollectionSyncTest extends TestCase
{
    public function testRemoveStackableUnitRemovesItFromLoadedRegionCollection(): void
    {
        $region = new WorldRegion();
        $unit = WorldRegionStackableUnit::create($region, GameUnitEnum::SOLDIER, 10);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('remove')->with($unit);
        $entityManager->expects(self::once())->method('flush');

        $repository = new DoctrineWorldRegionStackableUnitRepository($entityManager, new GameUnitRegistry());
        $repository->remove($unit);

        self::assertTrue($region->getWorldRegionStackableUnits()->isEmpty());
    }

    public function testRemoveLeveledUnitRemovesItFromLoadedRegionCollection(): void
    {
        $region = new WorldRegion();
        $unit = WorldRegionLeveledUnit::create($region, GameUnitEnum::BARRACK, 1, 6000);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('remove')->with($unit);
        $entityManager->expects(self::once())->method('flush');

        $repository = new DoctrineWorldRegionLeveledUnitRepository($entityManager, new GameUnitRegistry());
        $repository->remove($unit);

        self::assertNull($region->getLeveledUnit(GameUnitEnum::BARRACK));
    }

    public function testRemoveDoesNotLoadUninitializedRegionCollection(): void
    {
        $region = new WorldRegion();
        $unit = WorldRegionStackableUnit::create($region, GameUnitEnum::SOLDIER, 10);

        /** @var AbstractLazyCollection<int, WorldRegionStackableUnit> $lazyCollection */
        $lazyCollection = new class extends AbstractLazyCollection {
            protected function doInitialize(): void
            {
                $this->collection = new ArrayCollection();
            }
        };
        $region->setWorldRegionStackableUnits($lazyCollection);

        $repository = new DoctrineWorldRegionStackableUnitRepository(
            $this->createMock(EntityManagerInterface::class),
            new GameUnitRegistry()
        );
        $repository->remove($unit);

        self::assertFalse($lazyCollection->isInitialized());
    }

    public function testRemoveConstructionRemovesItFromLoadedRegionCollection(): void
    {
        $region = new WorldRegion();
        $construction = Construction::create($region, new Player(), GameUnitEnum::FARM, 5, 3600);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('remove')->with($construction);
        $entityManager->expects(self::once())->method('flush');

        $repository = new DoctrineConstructionRepository($entityManager, new GameUnitRegistry());
        $repository->remove($construction);

        self::assertTrue($region->getConstructions()->isEmpty());
    }
}
