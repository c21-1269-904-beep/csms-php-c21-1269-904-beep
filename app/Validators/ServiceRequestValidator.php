<?php

namespace App\Validators;

class ServiceRequestValidator
{
    public function validate(array $data): ServiceRequestValidationResult
    {
        $errors = [];

        // Rule 1: ID must be unassigned prior to submission
        if (isset($data['id']) && $data['id'] !== null) {
            $errors['id'] = 'New service request must have an unassigned ID.';
        }

        // Rule 2: resident_id required & positive
        if (!isset($data['resident_id']) || !is_numeric($data['resident_id']) || $data['resident_id'] <= 0) {
            $errors['resident_id'] = 'Valid Resident ID is required.';
        }

        // Rule 3: service_type required and non-whitespace
        if (!isset($data['service_type']) || trim($data['service_type']) === '') {
            $errors['service_type'] = 'Service type is required.';
        }

        // Rule 4: description required and non-whitespace
        if (!isset($data['description']) || trim($data['description']) === '') {
            $errors['description'] = 'Description is required.';
        }

        // Rule 5: date_requested required
        if (!isset($data['date_requested']) || trim($data['date_requested']) === '') {
            $errors['date_requested'] = 'Date requested is required.';
        }

        // Rule 6: Initial status must be Pending
        if (isset($data['status']) && $data['status'] !== 'Pending') {
            $errors['status'] = 'Initial status must be Pending.';
        }

        if (!empty($errors)) {
            return ServiceRequestValidationResult::failure($errors);
        }

        return ServiceRequestValidationResult::success();
    }
}