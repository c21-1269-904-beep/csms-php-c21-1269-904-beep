<?php

namespace App\Services;

use App\Repositories\ServiceRequestRepository;

class ServiceRequestStatusService
{
    protected ServiceRequestRepository $repository;

    protected array $supportedStatuses = [
        'Pending',
        'In Progress',
        'Completed',
        'Cancelled',
    ];

    protected array $allowedTransitions = [
        'Pending' => ['In Progress', 'Cancelled'],
        'In Progress' => ['Completed', 'Cancelled'],
        'Completed' => [],
        'Cancelled' => [],
    ];

    public function __construct(?ServiceRequestRepository $repository = null)
    {
        $this->repository = $repository ?? new ServiceRequestRepository();
    }

    public function updateStatus(int|string $id, string $requestedStatus): ServiceRequestStatusResult
    {
        // 1. Retrieve Service Request
        $request = $this->repository->findById($id);
        if (!$request) {
            return ServiceRequestStatusResult::notFound();
        }

        // 2. Validate supported status
        if (!in_array($requestedStatus, $this->supportedStatuses, true)) {
            return ServiceRequestStatusResult::unsupportedStatus($requestedStatus);
        }

        $currentStatus = $request->status;

        // 3. Reject same-status requests
        if ($currentStatus === $requestedStatus) {
            return ServiceRequestStatusResult::invalidTransition($currentStatus, $requestedStatus);
        }

        // 4. Enforce transition rules
        $allowedNext = $this->allowedTransitions[$currentStatus] ?? [];
        if (!in_array($requestedStatus, $allowedNext, true)) {
            return ServiceRequestStatusResult::invalidTransition($currentStatus, $requestedStatus);
        }

        // 5. Update only status and save
        $request->status = $requestedStatus;
        $request->save();

        return ServiceRequestStatusResult::success($request);
    }
}