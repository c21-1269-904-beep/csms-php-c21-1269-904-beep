<?php

namespace App\Repositories;

use App\Models\ServiceRequest;

class ServiceRequestRepository
{
    public function save(array $data): ServiceRequest
    {
        return ServiceRequest::create($data);
    }

    public function findById(int|string $id): ?ServiceRequest
    {
        return ServiceRequest::find($id);
    }
}