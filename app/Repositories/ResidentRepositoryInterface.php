<?php

namespace App\Repositories;

use App\Models\Resident;

interface ResidentRepositoryInterface
{
    public function save(Resident $resident): Resident;

    public function findById(string $id): ?Resident;

    /**
     * Retrieve all persisted residents sorted deterministically.
     *
     * @return Resident[]
     */
    public function findAll(): array;

    /**
     * Search persisted residents by partial first or last name (case-insensitive).
     *
     * @param string $searchTerm
     * @return Resident[]
     */
    public function searchByName(string $searchTerm): array;
}