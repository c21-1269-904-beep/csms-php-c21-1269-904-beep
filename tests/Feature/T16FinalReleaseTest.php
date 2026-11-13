<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Resident;
use App\Repositories\ServiceRequestRepository;
use App\Services\DashboardService;
use App\Services\ServiceRequestStatusService;
use App\Services\ServiceRequestSubmissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class T16FinalReleaseTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Verify the health endpoint identifies the CSMS v1.0.0 release.
     */
    public function test_01_health_endpoint_returns_release_identity_and_version()
    {
        $response = $this->get('/health');

        $response->assertOk()
            ->assertExactJson([
                'application' => config('app.name'),
                'version' => '1.0.0',
                'status' => 'ok',
            ]);
    }

    /**
     * Verify a complete resident-to-service-request lifecycle
     * across resident management, submission, workflow,
     * persistence, and dashboard reporting.
     */
    public function test_02_resident_service_request_lifecycle_is_persisted_and_reported()
    {
        // Responsibility 1: Resident Management
        $resident = Resident::factory()->create([
            'first_name' => 'T16',
            'last_name' => 'Integration Resident',
            'status' => 'Active',
        ]);

        $this->assertDatabaseHas('residents', [
            'id' => $resident->id,
            'status' => 'Active',
        ]);

        // Responsibility 2: Service Request Submission
        $submissionService = new ServiceRequestSubmissionService();

        $submission = $submissionService->submit([
            'resident_id' => $resident->id,
            'service_type' => 'Barangay Clearance',
            'description' => 'T16 final release integration verification.',
            'date_requested' => '2026-11-13',
        ]);

        $this->assertTrue($submission->isSuccess());

        $requestId = $submission->serviceRequest->id;

        $this->assertDatabaseHas('service_requests', [
            'id' => $requestId,
            'resident_id' => $resident->id,
            'service_type' => 'Barangay Clearance',
            'status' => 'Pending',
        ]);

        // Responsibility 3: Service Request Workflow
        $statusService = new ServiceRequestStatusService();

        $inProgress = $statusService->updateStatus(
            $requestId,
            'In Progress'
        );

        $this->assertTrue($inProgress->isSuccess());

        $completed = $statusService->updateStatus(
            $requestId,
            'Completed'
        );

        $this->assertTrue($completed->isSuccess());

        // Invalid transition must not change persisted data.
        $invalidTransition = $statusService->updateStatus(
            $requestId,
            'Pending'
        );

        $this->assertTrue($invalidTransition->isInvalidTransition());

        // Responsibility 4: Verify actual persisted final state.
        $persistedRequest = (new ServiceRequestRepository())
            ->findById($requestId);

        $this->assertNotNull($persistedRequest);
        $this->assertEquals($resident->id, $persistedRequest->resident_id);
        $this->assertEquals('Barangay Clearance', $persistedRequest->service_type);
        $this->assertEquals(
            'T16 final release integration verification.',
            $persistedRequest->description
        );
        $this->assertEquals('Completed', $persistedRequest->status);

        // Resident information must remain unchanged.
        $this->assertEquals('Active', $resident->fresh()->status);
        $this->assertEquals('T16', $resident->fresh()->first_name);

        // Responsibility 5: Verify dashboard reporting from persisted data.
        $dashboard = (new DashboardService())->getSummary();

        $this->assertGreaterThanOrEqual(1, $dashboard['residentTotal']);
        $this->assertGreaterThanOrEqual(1, $dashboard['activeResidents']);
        $this->assertGreaterThanOrEqual(1, $dashboard['serviceRequestTotal']);
        $this->assertGreaterThanOrEqual(1, $dashboard['completedRequests']);

        $this->assertEquals(0, $dashboard['pendingRequests']);
        $this->assertEquals(0, $dashboard['inProgressRequests']);
        $this->assertEquals(0, $dashboard['openRequests']);
    }
}
