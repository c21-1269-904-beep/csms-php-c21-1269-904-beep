<?php

namespace App\Repositories;

use App\Models\ServiceRequest;
use Illuminate\Support\Facades\DB;

class ServiceRequestRepository
{
    public function save(array $data): ServiceRequest
    {
        $id = DB::table('service_requests')->insertGetId([
            'resident_id' => $data['resident_id'],
            'service_type' => $data['service_type'],
            'description' => $data['description'],
            'date_requested' => $data['date_requested'],
            'status' => $data['status'] ?? 'Pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $serviceRequest = new ServiceRequest(
            (int) $data['resident_id'],
            $data['service_type'],
            $data['description'],
            $data['date_requested']
        );

        $serviceRequest->id = (int) $id;
        $serviceRequest->status = $data['status'] ?? 'Pending';

        return $serviceRequest;
    }

    public function findById(int|string $id): ?ServiceRequest
    {
        $record = DB::table('service_requests')
            ->where('id', $id)
            ->first();

        if (!$record) {
            return null;
        }

        $serviceRequest = new ServiceRequest(
            (int) $record->resident_id,
            $record->service_type,
            $record->description,
            $record->date_requested
        );

        $serviceRequest->id = (int) $record->id;
        $serviceRequest->status = $record->status;

        return $serviceRequest;
    }

    public function update(int|string $id, array $data): ?ServiceRequest
    {
        $request = $this->findById($id);

        if (!$request) {
            return null;
        }

        $updates = [];

        if (isset($data['status'])) {
            $updates['status'] = $data['status'];
        }

        if (!empty($updates)) {
            $updates['updated_at'] = now();

            DB::table('service_requests')
                ->where('id', $id)
                ->update($updates);
        }

        return $this->findById($id);
    }
}
