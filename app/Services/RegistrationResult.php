<?php

namespace App\Services;

use App\Models\Resident;

class RegistrationResult
{
    private bool $success;
    private ?Resident $resident;
    private array $errors;

    private function __construct(bool $success, ?Resident $resident = null, array $errors = [])
    {
        $this->success = $success;
        $this->resident = $resident;
        $this->errors = $errors;
    }

    public static function success(Resident $resident): self
    {
        return new self(true, $resident, []);
    }

    public static function failure(array $errors): self
    {
        return new self(false, null, $errors);
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getResident(): ?Resident
    {
        return $this->resident;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function hasErrorForField(string $field): bool
    {
        return isset($this->errors[$field]);
    }
}