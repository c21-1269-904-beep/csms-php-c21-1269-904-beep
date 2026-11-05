<?php

namespace App\Validators;

use App\Validators\ResidentValidationResult;

class ResidentValidator
{
    public function validate(array $data): ResidentValidationResult
    {
        $errors = [];

        if (empty($data['first_name'] ?? null)) {
            $errors['first_name'] = 'First name is required.';
        }

        if (empty($data['last_name'] ?? null)) {
            $errors['last_name'] = 'Last name is required.';
        }

        if (empty($data['address'] ?? null)) {
            $errors['address'] = 'Address is required.';
        }

        if (empty($data['contact_number'] ?? null) || !preg_match('/^09\d{9}$/', (string)$data['contact_number'])) {
            $errors['contact_number'] = 'Invalid contact number format.';
        }

        if (empty($data['email'] ?? null) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email address.';
        }

        if (!empty($errors)) {
            return ResidentValidationResult::failure($errors);
        }

        return ResidentValidationResult::success();
    }
}