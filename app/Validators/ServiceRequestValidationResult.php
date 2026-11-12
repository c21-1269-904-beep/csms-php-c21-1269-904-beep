<?php

namespace App\Validators;

class ServiceRequestValidationResult
{
    public bool $isValid;
    public array $errors;

    public function __construct(bool $isValid, array $errors = [])
    {
        $this->isValid = $isValid;
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
}