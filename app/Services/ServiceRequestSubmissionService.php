<?php

namespace App\Services;

use App\Repositories\ResidentRepository;
use App\Repositories\ServiceRequestRepository;
use App\Validators\ServiceRequestValidator;

class ServiceRequestSubmissionService
{
    protected ServiceRequestValidator $validator;
    protected ResidentRepository $residentRepository;
    protected ServiceRequestRepository $serviceRequestRepository;

    public function __construct(
        ?ServiceRequestValidator $validator = null,
        ?ResidentRepository $residentRepository = null,
        ?ServiceRequestRepository $serviceRequestRepository = null
    ) {
        $this->validator = $validator ?? new ServiceRequestValidator();
        $this->residentRepository = $residentRepository ?? new ResidentRepository();
        $this->serviceRequestRepository = $serviceRequestRepository ?? new ServiceRequestRepository();
    }

    public function submit(array $data): ServiceRequestSubmissionResult
    {
        // 1. Validate intrinsic data
        $validation = $this->validator->validate($data);
        if (!$validation->isValid) {
            return ServiceRequestSubmissionResult::validationFailed($validation->errors);
        }

        // 2. Verify Resident existence
        $resident = $this->residentRepository->findById($data['resident_id']);
        if (!$resident) {
            return ServiceRequestSubmissionResult::residentNotFound();
        }

        // 3. Verify Resident is Active
        if ($resident->status === 'Inactive') {
            return ServiceRequestSubmissionResult::residentInactive();
        }

        // 4. Force status to Pending & Persist
        $data['status'] = 'Pending';
        $serviceRequest = $this->serviceRequestRepository->save($data);

        return ServiceRequestSubmissionResult::success($serviceRequest);
    }
}