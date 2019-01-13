<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity\Player;

use FrankProjects\UltimateWarfare\Entity\AbstractGameResources;

class Income extends AbstractGameResources
{
    /**
     * @param Income $income
     * @return bool
     */
    public function equals(Income $income): bool
    {
        if (
            $income->cash === $this->cash &&
            $income->food === $this->food &&
            $income->steel === $this->steel &&
            $income->wood === $this->wood
        ) {
            return true;
        }
        return false;
    }
}
