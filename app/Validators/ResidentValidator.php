<?php

namespace App\Validators;

class ResidentValidator
{
    public function validate(array $data): ResidentValidationResult
    {
        $errors = [];

        if (!isset($data['first_name']) || trim($data['first_name']) === '') {
            $errors['first_name'] = 'First name is required.';
        }

        if (!isset($data['last_name']) || trim($data['last_name']) === '') {
            $errors['last_name'] = 'Last name is required.';
        }

        if (!isset($data['address']) || trim($data['address']) === '') {
            $errors['address'] = 'Address is required.';
        }

        if (!isset($data['contact_number']) || trim($data['contact_number']) === '') {
            $errors['contact_number'] = 'Contact number is required.';
        }

        if (!isset($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Valid email is required.';
        }

        if (!empty($errors)) {
            return ResidentValidationResult::failure($errors);
        }

        return ResidentValidationResult::success();
    }

    public function validateUpdate(array $data, int|string $id): ResidentValidationResult
    {
        return $this->validate($data);
    }
}