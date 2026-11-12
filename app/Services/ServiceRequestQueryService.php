<?php

namespace App\Services;

use App\Models\ServiceRequest;
use App\Repositories\ServiceRequestRepository;

class ServiceRequestQueryService
{
    protected ServiceRequestRepository $repository;

    protected array $supportedStatuses = [
        'Pending',
        'In Progress',
        'Completed',
        'Cancelled',
    ];

    public function __construct(?ServiceRequestRepository $repository = null)
    {
        $this->repository = $repository ?? new ServiceRequestRepository();
    }

    /**
     * List all persisted service requests.
     *
     * @return ServiceRequest[]
     */
    public function listAll(): array
    {
        return $this->repository->findAll();
    }

    /**
     * View one service request by ID.
     */
    public function viewById(int|string $id): ?ServiceRequest
    {
        return $this->repository->findById($id);
    }

    /**
     * Search by service type or description,
     * optionally combined with a status filter.
     */
    public function search(
        ?string $searchTerm = null,
        ?string $status = null
    ): ServiceRequestQueryResult {

        // Trim search input.
        $searchTerm = $searchTerm === null
            ? null
            : trim($searchTerm);

        // Blank search means list all.
        if ($searchTerm === '') {
            $searchTerm = null;
        }

        // Trim status input.
        $status = $status === null
            ? null
            : trim($status);

        // Blank status means no status filter.
        if ($status === '') {
            $status = null;
        }

        // Reject unsupported non-empty status filters.
        if (
            $status !== null &&
            !in_array($status, $this->supportedStatuses, true)
        ) {
            return ServiceRequestQueryResult::unsupportedStatus($status);
        }

        // Perform filtering through the repository/database.
        $requests = $this->repository->search(
            $searchTerm,
            $status
        );

        return ServiceRequestQueryResult::success($requests);
    }
}
