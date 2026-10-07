<?php

namespace App\Services;

use App\Repositories\ResidentRepository;
use App\Validators\ResidentValidator;

class ResidentUpdateService
{
    protected ResidentRepository $repository;
    protected ResidentValidator $validator;

    public function __construct(?ResidentRepository $repository = null, ?ResidentValidator $validator = null)
    {
        $this->repository = $repository ?? new ResidentRepository();
        $this->validator = $validator ?? new ResidentValidator();
    }

    public function update(int|string $id, array $data): ResidentUpdateResult
    {
        $resident = $this->repository->findById($id);
        if (!$resident) {
            return ResidentUpdateResult::notFound();
        }

        $validation = $this->validator->validateUpdate($data, $id);
        if (!$validation->isValid) {
            return ResidentUpdateResult::validationFailed($validation->errors);
        }

        $updatedResident = $this->repository->update($id, $data);

        return ResidentUpdateResult::success($updatedResident);
    }
}