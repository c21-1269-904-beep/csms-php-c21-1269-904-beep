<?php

namespace App\Services;

use App\Repositories\ResidentRepository;
use App\Validators\ResidentValidator;
use App\Models\Resident;

class ResidentUpdateService
{
    private ResidentRepository $repository;
    private ResidentValidator $validator;

    public function __construct(?ResidentRepository $repository = null, ?ResidentValidator $validator = null)
    {
        $this->repository = $repository ?? new ResidentRepository();
        $this->validator = $validator ?? new ResidentValidator();
    }

    public function update(int $id, array $data): ResidentUpdateResult
    {
        // 1. Retrieve existing resident by ID
        $existing = $this->repository->findById($id);
        if (!$existing) {
            return ResidentUpdateResult::notFound();
        }

        // 2. Build update payload while preserving ID and Status
        $updatePayload = array_merge($data, [
            'id' => $existing->id,
            'status' => $existing->status,
        ]);

        // 3. Validate using T02 ResidentValidator
        $validationResult = $this->validator->validate($updatePayload);
        if (!$validationResult->isValid()) {
            return ResidentUpdateResult::validationFailed($validationResult->getErrors());
        }

        // 4. Update and persist changes
        $updatedResident = $this->repository->update($id, [
            'first_name'     => $data['first_name'] ?? $existing->first_name,
            'last_name'      => $data['last_name'] ?? $existing->last_name,
            'address'        => $data['address'] ?? $existing->address,
            'contact_number' => (string) ($data['contact_number'] ?? $existing->contact_number),
            'email'          => $data['email'] ?? $existing->email,
        ]);

        return ResidentUpdateResult::success($updatedResident);
    }
}