<?php

namespace App\Services;

use App\Models\Resident;
use App\Repositories\ResidentRepository;

class ResidentDeactivationService
{
    protected ?ResidentRepository $repository;

    public function __construct(?ResidentRepository $repository = null)
    {
        $this->repository = $repository;
    }

    public function deactivate(int|string $id): ResidentDeactivationResult
    {
        $resident = $this->repository 
            ? $this->repository->findById($id) 
            : Resident::find($id);

        if (!$resident) {
            return ResidentDeactivationResult::notFound();
        }

        if ($resident->status === 'Inactive') {
            return ResidentDeactivationResult::alreadyInactive($resident);
        }

        if ($this->repository) {
            $this->repository->deactivateById($id);
            $resident = $this->repository->findById($id);
        } else {
            $resident->update(['status' => 'Inactive']);
        }

        return ResidentDeactivationResult::success($resident);
    }
}