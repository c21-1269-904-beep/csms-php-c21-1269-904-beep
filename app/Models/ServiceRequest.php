<?php

namespace App\Models;

class ServiceRequest
{
    public ?int $id;

    public int $resident_id;

    public string $service_type;

    public string $description;

    public string $date_requested;

    public string $status;

    public function __construct(
        int $resident_id,
        string $service_type,
        string $description,
        string $date_requested
    ) {
        $this->id = null;
        $this->resident_id = $resident_id;
        $this->service_type = $service_type;
        $this->description = $description;
        $this->date_requested = $date_requested;
        $this->status = 'Pending';
    }
}
