<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Repository;

use FrankProjects\UltimateWarfare\Entity\Contact;

interface ContactRepository
{
    /**
     * @param int $id
     * @return Contact|null
     */
    public function find(int $id): ?Contact;

    /**
     * @return Contact[]
     */
    public function findAll(): array;

    /**
     * @param Contact $contact
     */
    public function remove(Contact $contact): void;

    /**
     * @param Contact $contact
     */
    public function save(Contact $contact): void;
}
