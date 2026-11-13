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

        return $record ? $this->mapToServiceRequest($record) : null;
    }

    public function findAll(): array
    {
        $records = DB::table('service_requests')
            ->orderByDesc('date_requested')
            ->orderByDesc('id')
            ->get();

        return $records->map(
            fn ($record) => $this->mapToServiceRequest($record)
        )->all();
    }

    public function search(
        ?string $searchTerm = null,
        ?string $status = null
    ): array {
        $query = DB::table('service_requests');

        if ($searchTerm !== null && $searchTerm !== '') {
            $term = '%' . mb_strtolower($searchTerm) . '%';

            $query->where(function ($subQuery) use ($term) {
                $subQuery
                    ->whereRaw('LOWER(service_type) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(description) LIKE ?', [$term]);
            });
        }

        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        }

        $records = $query
            ->orderByDesc('date_requested')
            ->orderByDesc('id')
            ->get();

        return $records->map(
            fn ($record) => $this->mapToServiceRequest($record)
        )->all();
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

    /**
     * Get the total number of service requests.
     */
    public function countAll(): int
    {
        return DB::table('service_requests')->count();
    }

    /**
     * Get the number of service requests with a specific status.
     */
    public function countByStatus(string $status): int
    {
        return DB::table('service_requests')
            ->where('status', $status)
            ->count();
    }

    /**
     * Get the most recent service requests.
     */
    public function findRecent(int $limit = 5): array
    {
        $records = DB::table('service_requests')
            ->orderByDesc('date_requested')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();

        return $records->map(
            fn ($record) => $this->mapToServiceRequest($record)
        )->all();
    }

    private function mapToServiceRequest(object $record): ServiceRequest
    {
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
}
