<?php

namespace App\Services;

use App\Models\Resident;

class ResidentUpdateResult
{
    public const SUCCESS = 'success';
    public const NOT_FOUND = 'not_found';
    public const VALIDATION_FAILED = 'validation_failed';

    public string $status;
    public ?Resident $resident;
    public array $errors;

    public function __construct(string $status, ?Resident $resident = null, array $errors = [])
    {
        $this->status = $status;
        $this->resident = $resident;
        $this->errors = $errors;
    }

    public static function success(Resident $resident): self
    {
        return new self(self::SUCCESS, $resident);
    }

    public static function notFound(): self
    {
        return new self(self::NOT_FOUND, null, ['id' => 'Resident not found.']);
    }

    public static function validationFailed(array $errors): self
    {
        return new self(self::VALIDATION_FAILED, null, $errors);
    }

    public function isSuccess(): bool
    {
        return $this->status === self::SUCCESS;
    }

    public function isNotFound(): bool
    {
        return $this->status === self::NOT_FOUND;
    }

    public function isValidationFailed(): bool
    {
        return $this->status === self::VALIDATION_FAILED;
    }

    public function getResident(): ?Resident
    {
        return $this->resident;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getStatus(): string
    {
        return $this->status;
    }
}