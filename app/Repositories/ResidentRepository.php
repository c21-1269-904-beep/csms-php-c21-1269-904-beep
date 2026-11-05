<?php

namespace App\Repositories;

use App\Models\Resident;

class ResidentRepository
{
    public function findById(int $id): ?Resident
    {
        return Resident::find($id);
    }

    public function update(int $id, array $data): Resident
    {
        $resident = $this->findById($id);

        if (!$resident) {
            throw new \InvalidArgumentException("Resident with ID {$id} not found.");
        }

        $resident->first_name     = $data['first_name'];
        $resident->last_name      = $data['last_name'];
        $resident->address        = $data['address'];
        $resident->contact_number = (string) $data['contact_number'];
        $resident->email          = $data['email'];

        $resident->save();

        return $resident;
    }
}