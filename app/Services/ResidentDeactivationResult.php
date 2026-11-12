<?php

namespace App\Services;

use App\Models\Resident;

class ResidentDeactivationResult
{
    public const SUCCESS = 'success';
    public const ALREADY_INACTIVE = 'already_inactive';
    public const NOT_FOUND = 'not_found';

    private string $status;
    private ?Resident $resident;

    private function __construct(string $status, ?Resident $resident = null)
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
        return new self(self::NOT_FOUND);
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getResident(): ?Resident
    {
        return $this->resident;
    }

    public function isSuccess(): bool
    {
        return in_array(
            $this->status,
            [self::SUCCESS, self::ALREADY_INACTIVE],
            true
        );
    }
}
