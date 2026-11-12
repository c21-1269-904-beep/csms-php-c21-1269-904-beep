<?php

namespace App\Services;

use App\Models\Resident;
use App\Repositories\ResidentRepository;
use App\Validators\ResidentValidator;

class ResidentRegistrationService
{
    private ResidentRepository $repository;
    private ResidentValidator $validator;

    public function __construct(
        ?ResidentRepository $repository = null,
        ?ResidentValidator $validator = null
    ) {
        $this->repository = $repository ?? new ResidentRepository();
        $this->validator = $validator ?? new ResidentValidator();
    }

    public function register(array $data): RegistrationResult
    {
        $validationResult = $this->validator->validate($data);

        if (!$validationResult->isValid()) {
            return RegistrationResult::failure(
                $validationResult->getErrors()
            );
        }

        $resident = new Resident([
            'first_name' => trim($data['first_name']),
            'last_name' => trim($data['last_name']),
            'address' => trim($data['address']),
            'contact_number' => trim((string) $data['contact_number']),
            'email' => trim($data['email']),
        ]);

        // Status is intentionally omitted; the database defaults it to Active.
        $savedResident = $this->repository->save($resident);

        return RegistrationResult::success($savedResident);
    }
}
