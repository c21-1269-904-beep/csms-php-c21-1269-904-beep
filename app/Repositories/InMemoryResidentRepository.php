<?php

namespace App\Repositories;

use App\Models\Resident;

class InMemoryResidentRepository implements ResidentRepositoryInterface
{
    private array $residents = [];

    public function save(Resident $resident): Resident
    {
        if ($resident->getId() === null) {
            $resident->setId(uniqid('res_'));
        }
        $this->residents[$resident->getId()] = $resident;
        return $resident;
    }

    public function findById(string $id): ?Resident
    {
        return $this->residents[$id] ?? null;
    }

    public function findAll(): array
    {
        $list = array_values($this->residents);
        $this->sortResidents($list);
        return $list;
    }

    public function searchByName(string $searchTerm): array
    {
        $normalizedSearch = mb_strtolower(trim($searchTerm));

        if ($normalizedSearch === '') {
            return $this->findAll();
        }

        $results = [];
        foreach ($this->residents as $resident) {
            $firstName = mb_strtolower($resident->getFirstName());
            $lastName  = mb_strtolower($resident->getLastName());

            if (str_contains($firstName, $normalizedSearch) || str_contains($lastName, $normalizedSearch)) {
                $results[] = $resident;
            }
        }

        $this->sortResidents($results);
        return $results;
    }

    /**
     * Sorts residents deterministically:
     * 1. lastName ascending (case-insensitive)
     * 2. firstName ascending (case-insensitive)
     * 3. id ascending (case-insensitive tie-breaker)
     */
    private function sortResidents(array &$residents): void
    {
        usort($residents, function (Resident $a, Resident $b) {
            $cmpLast = strcasecmp($a->getLastName(), $b->getLastName());
            if ($cmpLast !== 0) {
                return $cmpLast;
            }

            $cmpFirst = strcasecmp($a->getFirstName(), $b->getFirstName());
            if ($cmpFirst !== 0) {
                return $cmpFirst;
            }

            return strcasecmp((string) $a->getId(), (string) $b->getId());
        });
    }
}