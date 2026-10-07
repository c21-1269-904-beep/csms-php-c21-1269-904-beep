<?php

namespace App\Services;

use App\Models\Resident;

class ResidentUpdateResult
{
    private bool $success;
    private bool $notFound;
    private ?Resident $resident;
    private array $errors;

    private function __construct(bool $success, bool $notFound, ?Resident $resident = null, array $errors = [])
    {
        $this->success = $success;
        $this->notFound = $notFound;
        $this->resident = $resident;
        $this->errors = $errors;
    }

    public static function success(Resident $resident): self
    {
        return new self(true, false, $resident, []);
    }

    public static function notFound(): self
    {
        return new self(false, true, null, []);
    }

    public static function validationFailed(array $errors): self
    {
        return new self(false, false, null, $errors);
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function isNotFound(): bool
    {
        return $this->notFound;
    }

    public function isValidationFailed(): bool
    {
        return !$this->success && !$this->notFound;
    }

    public function getResident(): ?Resident
    {
        return $this->resident;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}