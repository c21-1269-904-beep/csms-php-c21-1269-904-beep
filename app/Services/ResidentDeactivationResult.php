<?php

namespace App\Services;

use App\Models\Resident;

class ResidentDeactivationResult
{
    public const SUCCESS = 'success';
    public const ALREADY_INACTIVE = 'already_inactive';
    public const NOT_FOUND = 'not_found';

    public string $status;
    public ?Resident $resident;

    public function __construct(string $status, ?Resident $resident = null)
    {
        $this->status = $status;
        $this->resident = $resident;
    }

    public static function success(Resident $resident): self
    {
        return new self(self::SUCCESS, $resident);
    }

    public static function alreadyInactive(Resident $resident): self
    {
        return new self(self::ALREADY_INACTIVE, $resident);
    }

    public static function notFound(): self
    {
        return new self(self::NOT_FOUND, null);
    }

    public function isSuccess(): bool
    {
        return $this->status === self::SUCCESS || $this->status === self::ALREADY_INACTIVE;
    }
}