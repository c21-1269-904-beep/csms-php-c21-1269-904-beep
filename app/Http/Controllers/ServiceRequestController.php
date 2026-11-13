<?php

namespace App\Http\Controllers;

use App\Services\ServiceRequestQueryService;
use App\Services\ServiceRequestSubmissionService;
use App\Services\ServiceRequestStatusService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ServiceRequestController extends Controller
{
    protected ServiceRequestQueryService $queryService;
    protected ServiceRequestSubmissionService $submissionService;
    protected ServiceRequestStatusService $statusService;

    public function __construct(
        ServiceRequestQueryService $queryService,
        ServiceRequestSubmissionService $submissionService,
        ServiceRequestStatusService $statusService
    ) {
        $this->queryService = $queryService;
        $this->submissionService = $submissionService;
        $this->statusService = $statusService;
    }

    public function index(Request $request): View
    {
        $search = $request->query('search');
        $status = $request->query('status');

        $result = $this->queryService->search($search, $status);

        return view('service-requests.index', [
            'serviceRequests' => $result->isSuccess()
                ? $result->getServiceRequests()
                : [],
            'search' => $search,
            'selectedStatus' => $status,
            'queryErrors' => $result->isSuccess()
                ? []
                : $result->getErrors(),
        ]);
    }

    public function create(): View
    {
        return view('service-requests.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $result = $this->submissionService->submit([
            'resident_id' => $request->input('resident_id'),
            'service_type' => $request->input('service_type'),
            'description' => $request->input('description'),
            'date_requested' => $request->input('date_requested'),
        ]);

        if (!$result->isSuccess()) {
            return redirect()
                ->back()
                ->withErrors($result->errors)
                ->withInput();
        }

        return redirect()
            ->route('service-requests.index')
            ->with('success', 'Service request submitted successfully.');
    }

    public function show(int $id): View
    {
        $serviceRequest = $this->queryService->viewById($id);

        abort_if(!$serviceRequest, 404);

        return view('service-requests.show', [
            'serviceRequest' => $serviceRequest,
        ]);
    }

    public function updateStatus(Request $request, int $id): RedirectResponse
    {
        $result = $this->statusService->updateStatus(
            $id,
            (string) $request->input('status')
        );

        if ($result->isNotFound()) {
            abort(404);
        }

        if (!$result->isSuccess()) {
            return redirect()
                ->back()
                ->withErrors($result->errors);
        }

        return redirect()
            ->route('service-requests.show', $id)
            ->with('success', 'Service request status updated successfully.');
    }
}
