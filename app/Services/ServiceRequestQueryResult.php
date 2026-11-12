<?php

namespace App\Services;

use App\Models\ServiceRequest;

class ServiceRequestQueryResult
{
    public const SUCCESS = 'success';
    public const UNSUPPORTED_STATUS = 'unsupported_status';

    public string $status;

    /** @var ServiceRequest[] */
    public array $serviceRequests;

    public array $errors;

    public function __construct(
        string $status,
        array $serviceRequests = [],
        array $errors = []
    ) {
        $this->status = $status;
        $this->serviceRequests = $serviceRequests;
        $this->errors = $errors;
    }

    public static function success(array $serviceRequests): self
    {
        return new self(self::SUCCESS, $serviceRequests);
    }

    public static function unsupportedStatus(string $requestedStatus): self
    {
        return new self(
            self::UNSUPPORTED_STATUS,
            [],
            ['status' => "Status '{$requestedStatus}' is unsupported."]
        );
    }

    public function isSuccess(): bool
    {
        return $this->status === self::SUCCESS;
    }

    public function isUnsupportedStatus(): bool
    {
        return $this->status === self::UNSUPPORTED_STATUS;
    }

    /**
     * @return ServiceRequest[]
     */
    public function getServiceRequests(): array
    {
        return $this->serviceRequests;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
