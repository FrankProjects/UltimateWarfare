<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Entity\Player;

use FrankProjects\UltimateWarfare\Entity\AbstractGameResources;

class Upkeep extends AbstractGameResources
{
    /**
     * @param Upkeep $upkeep
     * @return bool
     */
    public function equals(Upkeep $upkeep): bool
    {
        if (
            $upkeep->cash === $this->cash &&
            $upkeep->food === $this->food &&
            $upkeep->steel === $this->steel &&
            $upkeep->wood === $this->wood
        ) {
            return true;
        }
        return false;
    }
}
