<?php

namespace App\Services;

use App\Models\ServiceRequest;

class ServiceRequestSubmissionResult
{
    public const SUCCESS = 'success';
    public const VALIDATION_FAILED = 'validation_failed';
    public const RESIDENT_NOT_FOUND = 'resident_not_found';
    public const RESIDENT_INACTIVE = 'resident_inactive';

    public string $status;
    public ?ServiceRequest $serviceRequest;
    public array $errors;

    public function __construct(string $status, ?ServiceRequest $serviceRequest = null, array $errors = [])
    {
        $this->status = $status;
        $this->serviceRequest = $serviceRequest;
        $this->errors = $errors;
    }

    public static function success(ServiceRequest $serviceRequest): self
    {
        return new self(self::SUCCESS, $serviceRequest);
    }

    public static function validationFailed(array $errors): self
    {
        return new self(self::VALIDATION_FAILED, null, $errors);
    }

    public static function residentNotFound(): self
    {
        return new self(self::RESIDENT_NOT_FOUND, null, ['resident_id' => 'Resident not found.']);
    }

    public static function residentInactive(): self
    {
        return new self(self::RESIDENT_INACTIVE, null, ['resident_id' => 'Inactive resident cannot submit requests.']);
    }

    public function isSuccess(): bool
    {
        return $this->status === self::SUCCESS;
    }
}