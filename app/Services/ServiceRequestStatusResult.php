<?php

namespace App\Services;

use App\Models\ServiceRequest;

class ServiceRequestStatusResult
{
    public const SUCCESS = 'success';
    public const NOT_FOUND = 'not_found';
    public const UNSUPPORTED_STATUS = 'unsupported_status';
    public const INVALID_TRANSITION = 'invalid_transition';

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

    public static function notFound(): self
    {
        return new self(self::NOT_FOUND, null, ['id' => 'Service Request not found.']);
    }

    public static function unsupportedStatus(string $requestedStatus): self
    {
        return new self(self::UNSUPPORTED_STATUS, null, ['status' => "Status '{$requestedStatus}' is unsupported."]);
    }

    public static function invalidTransition(string $current, string $requested): self
    {
        return new self(self::INVALID_TRANSITION, null, ['status' => "Transition from '{$current}' to '{$requested}' is invalid."]);
    }

    public function isSuccess(): bool
    {
        return $this->status === self::SUCCESS;
    }

    public function isNotFound(): bool
    {
        return $this->status === self::NOT_FOUND;
    }

    public function isUnsupportedStatus(): bool
    {
        return $this->status === self::UNSUPPORTED_STATUS;
    }

    public function isInvalidTransition(): bool
    {
        return $this->status === self::INVALID_TRANSITION;
    }

    public function getServiceRequest(): ?ServiceRequest
    {
        return $this->serviceRequest;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}