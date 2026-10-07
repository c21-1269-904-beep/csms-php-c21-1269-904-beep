<?php

namespace App\Repositories;

use App\Models\ServiceRequest;

class ServiceRequestRepository
{
    public function save(array $data): ServiceRequest
    {
        $request = new ServiceRequest();
        $request->resident_id = $data['resident_id'];
        $request->service_type = $data['service_type'];
        $request->description = $data['description'];
        $request->date_requested = $data['date_requested'];
        $request->status = $data['status'] ?? 'Pending';
        $request->save();

        return $request;
    }

    public function findById(int|string $id): ?ServiceRequest
    {
        return ServiceRequest::find($id);
    }

    public function update(int|string $id, array $data): ?ServiceRequest
    {
        $request = $this->findById($id);
        if (!$request) {
            return null;
        }

        if (isset($data['status'])) {
            $request->status = $data['status'];
        }

        $request->save();
        return $request;
    }
}