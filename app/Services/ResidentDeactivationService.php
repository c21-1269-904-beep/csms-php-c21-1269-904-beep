<?php

namespace App\Services;

use App\Repositories\ResidentRepository;

class ResidentDeactivationService
{
    private ResidentRepository $repository;

    public function __construct(?ResidentRepository $repository = null)
    {
        $this->repository = $repository ?? new ResidentRepository();
    }

    public function deactivate(int|string $id): ResidentDeactivationResult
    {
        $resident = $this->repository->findById($id);

        if (!$resident) {
            return ResidentDeactivationResult::notFound();
        }

        if ($resident->status === 'Inactive') {
            return ResidentDeactivationResult::alreadyInactive($resident);
        }

        $this->repository->deactivateById($id);

        $updatedResident = $this->repository->findById($id);

        if (!$updatedResident) {
            return ResidentDeactivationResult::notFound();
        }

        return ResidentDeactivationResult::success($updatedResident);
    }
}
