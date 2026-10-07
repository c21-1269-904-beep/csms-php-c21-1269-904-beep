<?php

namespace App\Validators;

class ResidentValidationResult
{
    private bool $valid;
    private array $errors;

    public function __construct(bool $valid, array $errors = [])
    {
        $this->valid = $valid;
        $this->errors = $errors;
    }

    public static function success(): self
    {
        return new self(true, []);
    }

    public static function failure(array $errors): self
    {
        return new self(false, $errors);
    }

    public function isValid(): bool
    {
        return $this->valid;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}