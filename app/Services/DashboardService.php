<?php

namespace App\Services;

use App\Repositories\ResidentRepository;
use App\Repositories\ServiceRequestRepository;

class DashboardService
{
    private ResidentRepository $residentRepository;

    private ServiceRequestRepository $serviceRequestRepository;

    public function __construct(
        ?ResidentRepository $residentRepository = null,
        ?ServiceRequestRepository $serviceRequestRepository = null
    ) {
        $this->residentRepository = $residentRepository
            ?? new ResidentRepository();

        $this->serviceRequestRepository = $serviceRequestRepository
            ?? new ServiceRequestRepository();
    }

    /**
     * Retrieve all dashboard summary statistics.
     */
    public function getSummary(): array
    {
        $residentTotal = $this->residentRepository->countAll();
        $residentActive = $this->residentRepository->countByStatus('Active');
        $residentInactive = $this->residentRepository->countByStatus('Inactive');

        $serviceRequestTotal = $this->serviceRequestRepository->countAll();

        $pending = $this->serviceRequestRepository->countByStatus('Pending');
        $inProgress = $this->serviceRequestRepository->countByStatus('In Progress');
        $completed = $this->serviceRequestRepository->countByStatus('Completed');
        $cancelled = $this->serviceRequestRepository->countByStatus('Cancelled');

        return [
            'residentTotal' => $residentTotal,
            'activeResidents' => $residentActive,
            'inactiveResidents' => $residentInactive,

            'serviceRequestTotal' => $serviceRequestTotal,
            'pendingRequests' => $pending,
            'inProgressRequests' => $inProgress,
            'completedRequests' => $completed,
            'cancelledRequests' => $cancelled,

            'openRequests' => $pending + $inProgress,

            'recentServiceRequests' => $this->serviceRequestRepository
                ->findRecent(5),
        ];
    }
}
