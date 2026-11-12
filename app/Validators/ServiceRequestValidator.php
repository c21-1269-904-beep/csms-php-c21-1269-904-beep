<?php

namespace App\Validators;

class ServiceRequestValidator
{
    public function validate(array $data): ServiceRequestValidationResult
    {
        $errors = [];

        // Rule 1: ID must be unassigned prior to submission
        if (array_key_exists('id', $data) && $data['id'] !== null) {
            $errors['id'] = 'New service request must have an unassigned ID.';
        }

        // Rule 2: Resident ID must be a positive integer
        $residentId = filter_var(
            $data['resident_id'] ?? null,
            FILTER_VALIDATE_INT
        );

        if ($residentId === false || $residentId === null || $residentId <= 0) {
            $errors['resident_id'] = 'Valid positive Resident ID is required.';
        }

        // Rule 3: Service type is required
        if (
            !isset($data['service_type']) ||
            !is_string($data['service_type']) ||
            trim($data['service_type']) === ''
        ) {
            $errors['service_type'] = 'Service type is required.';
        }

        // Rule 4: Description is required
        if (
            !isset($data['description']) ||
            !is_string($data['description']) ||
            trim($data['description']) === ''
        ) {
            $errors['description'] = 'Description is required.';
        }

        // Rule 5: Date must be a real calendar date in YYYY-MM-DD format
        $date = $data['date_requested'] ?? null;

        if (
            !is_string($date) ||
            !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) ||
            !checkdate(
                (int) substr($date, 5, 2),
                (int) substr($date, 8, 2),
                (int) substr($date, 0, 4)
            )
        ) {
            $errors['date_requested'] = 'A valid date in YYYY-MM-DD format is required.';
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
