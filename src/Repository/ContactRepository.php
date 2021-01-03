<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\Contact;

interface ContactRepository
{
    public function find(int $id): ?Contact;

    /**
     * @return Contact[]
     */
    public function findAll(): array;

    public function remove(Contact $contact): void;

    public function save(Contact $contact): void;
}
