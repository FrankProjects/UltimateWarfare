<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity;

abstract class AbstractGameResources
{
    public int $cash = 0;
    public int $food = 0;
    public int $wood = 0;
    public int $steel = 0;

    public function setCash(int $cash): void
    {
        $this->cash = $cash;
    }

    public function getCash(): int
    {
        return $this->cash;
    }

    public function setFood(int $food): void
    {
        $this->food = $food;
    }

    public function getFood(): int
    {
        return $this->food;
    }

    public function setWood(int $wood): void
    {
        $this->wood = $wood;
    }

    public function getWood(): int
    {
        return $this->wood;
    }

    public function setSteel(int $steel): void
    {
        $this->steel = $steel;
    }

    public function getSteel(): int
    {
        return $this->steel;
    }

    public function addCash(int $cash): void
    {
        $this->cash += $cash;
    }

    public function addFood(int $food): void
    {
        $this->food += $food;
    }

    public function addWood(int $wood): void
    {
        $this->wood += $wood;
    }

    public function addSteel(int $steel): void
    {
        $this->steel += $steel;
    }

    public function equals(AbstractGameResources $abstractGameResources): bool
    {
        if (
            $abstractGameResources->cash === $this->cash &&
            $abstractGameResources->food === $this->food &&
            $abstractGameResources->steel === $this->steel &&
            $abstractGameResources->wood === $this->wood
        ) {
            return true;
        }
        return false;
    }

    public function getValueByName(string $name): int
    {
        $reflectionObject = new \ReflectionObject($this);
        try {
            $refProperty = $reflectionObject->getProperty($name);
        } catch (\ReflectionException $e) {
            throw new \RunTimeException("Unknown resource {$name}");
        }
        return $refProperty->getValue($this);
    }

    public function setValueByName(string $name, int $value): void
    {
        $reflectionObject = new \ReflectionObject($this);
        try {
            $refProperty = $reflectionObject->getProperty($name);
        } catch (\ReflectionException $e) {
            throw new \RunTimeException("Unknown resource {$name}");
        }
        $refProperty->setValue($this, $value);
    }
}
