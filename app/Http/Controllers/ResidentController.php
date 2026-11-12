<?php

namespace App\Http\Controllers;

use App\Repositories\ResidentRepository;
use App\Services\ResidentRegistrationService;
use App\Services\ResidentUpdateService;
use App\Services\ResidentDeactivationService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ResidentController extends Controller
{
    public function index(
        Request $request,
        ResidentRepository $repository
    ): View {
        $search = trim($request->query('search', ''));

        $residents = $repository->searchByName($search);

        return view('residents.index', [
            'residents' => $residents,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('residents.create');
    }

    public function store(
        Request $request,
        ResidentRegistrationService $service
    ): RedirectResponse {
        $result = $service->register($request->only([
            'first_name',
            'last_name',
            'address',
            'contact_number',
            'email',
        ]));

        if (!$result->isSuccess()) {
            return redirect()
                ->back()
                ->withErrors($result->getErrors())
                ->withInput();
        }

        return redirect()
            ->route('residents.index')
            ->with('success', 'Resident registered successfully.');
    }

    public function edit(
        int $id,
        ResidentRepository $repository
    ): View {
        $resident = $repository->findById($id);

        if (!$resident) {
            abort(404);
        }

        return view('residents.edit', compact('resident'));
    }

    public function update(
        Request $request,
        int $id,
        ResidentUpdateService $service
    ): RedirectResponse {
        $result = $service->update($id, $request->only([
            'first_name',
            'last_name',
            'address',
            'contact_number',
            'email',
        ]));

        if ($result->isNotFound()) {
            abort(404);
        }

        if ($result->isValidationFailed()) {
            return redirect()
                ->back()
                ->withErrors($result->getErrors())
                ->withInput();
        }

        return redirect()
            ->route('residents.index')
            ->with('success', 'Resident information updated successfully.');
    }

    public function deactivate(
        int $id,
        ResidentDeactivationService $service
    ): RedirectResponse {
        $result = $service->deactivate($id);

        if ($result->getStatus() === 'not_found') {
            abort(404);
        }

        if ($result->getStatus() === 'already_inactive') {
            return redirect()
                ->route('residents.index')
                ->with('info', 'This resident is already inactive.');
        }

        return redirect()
            ->route('residents.index')
            ->with('success', 'Resident deactivated successfully.');
    }
}
