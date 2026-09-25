<?php

namespace App\Services;

use App\Models\Resident;
use App\Validators\ResidentValidator;
use App\Repositories\ResidentRepositoryInterface;

class ResidentRegistrationService
{
    private ResidentValidator $validator;
    private ResidentRepositoryInterface $repository;

    public function __construct(ResidentValidator $validator, ResidentRepositoryInterface $repository)
    {
        $this->validator = $validator;
        $this->repository = $repository;
    }

    public function register(Resident $resident): RegistrationResult
    {
        // 1. Validate using T02 Validator
        $validationResult = $this->validator->validate($resident);

        // Check if validation failed (supports both array return or ValidationResult object)
        $isValid = is_array($validationResult) ? empty($validationResult) : $validationResult->isValid();
        $errors  = is_array($validationResult) ? $validationResult : $validationResult->getErrors();

        if (!$isValid) {
            return RegistrationResult::failure($errors);
        }

        // 2. Persist using T03 Repository (Only executed when valid)
        $savedResident = $this->repository->save($resident);

        // 3. Return successful registration result
        return RegistrationResult::success($savedResident);
    }
}