<?php

namespace App\Repositories;

use App\Models\Resident;
use Illuminate\Database\Eloquent\Collection;

class ResidentRepository
{
    public function save(Resident $resident): Resident
    {
        $resident->save();

        return $resident;
    }

    public function findById(int|string $id): ?Resident
    {
        return Resident::find($id);
    }

    public function findAll(): Collection
    {
        return Resident::query()
            ->orderByRaw('LOWER(last_name) ASC')
            ->orderByRaw('LOWER(first_name) ASC')
            ->orderBy('id', 'ASC')
            ->get();
    }

    public function searchByName(string $searchTerm): Collection
    {
        $searchTerm = mb_strtolower(trim($searchTerm));

        if ($searchTerm === '') {
            return $this->findAll();
        }

        return Resident::query()
            ->where(function ($query) use ($searchTerm) {
                $query->whereRaw(
                    'LOWER(first_name) LIKE ?',
                    ['%' . $searchTerm . '%']
                )->orWhereRaw(
                    'LOWER(last_name) LIKE ?',
                    ['%' . $searchTerm . '%']
                );
            })
            ->orderByRaw('LOWER(last_name) ASC')
            ->orderByRaw('LOWER(first_name) ASC')
            ->orderBy('id', 'ASC')
            ->get();
    }

    public function update(int $id, array $data): Resident
    {
        $resident = $this->findById($id);

        if (!$resident) {
            throw new \InvalidArgumentException(
                "Resident with ID {$id} not found."
            );
        }

        $resident->first_name = $data['first_name'];
        $resident->last_name = $data['last_name'];
        $resident->address = $data['address'];
        $resident->contact_number = (string) $data['contact_number'];
        $resident->email = $data['email'];

        $resident->save();

        return $resident;
    }

    public function deactivateById(int|string $id): bool
    {
        $resident = $this->findById($id);

        if (!$resident) {
            return false;
        }

        return $resident->update(['status' => 'Inactive']);
    }

    /**
     * Get the total number of residents.
     */
    public function countAll(): int
    {
        return Resident::query()->count();
    }

    /**
     * Get the number of residents with a specific status.
     */
    public function countByStatus(string $status): int
    {
        return Resident::query()
            ->where('status', $status)
            ->count();
    }
}
