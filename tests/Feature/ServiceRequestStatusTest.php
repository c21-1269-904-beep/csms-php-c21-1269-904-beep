<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Resident;
use App\Models\ServiceRequest;
use App\Services\ServiceRequestStatusService;
use App\Services\ServiceRequestSubmissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ServiceRequestStatusTest extends TestCase
{
    use RefreshDatabase;

    protected ServiceRequestStatusService $statusService;
    protected ServiceRequestSubmissionService $submissionService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->statusService = new ServiceRequestStatusService();
        $this->submissionService = new ServiceRequestSubmissionService();
    }

    // Test 1: Pending -> In Progress
    public function test_01_pending_can_move_to_in_progress()
    {
        $resident = Resident::factory()->create(['status' => 'Active']);
        $sub = $this->submissionService->submit([
            'resident_id' => $resident->id,
            'service_type' => 'Clearance',
            'description' => 'Desc',
            'date_requested' => '2026-10-08',
        ]);

        $result = $this->statusService->updateStatus($sub->serviceRequest->id, 'In Progress');

        $this->assertTrue($result->isSuccess());
        $this->assertEquals('In Progress', $result->getServiceRequest()->status);
        $this->assertEquals('In Progress', ServiceRequest::find($sub->serviceRequest->id)->status);
    }

    // Test 2: Pending -> Cancelled
    public function test_02_pending_can_move_to_cancelled()
    {
        $resident = Resident::factory()->create(['status' => 'Active']);
        $sub = $this->submissionService->submit([
            'resident_id' => $resident->id,
            'service_type' => 'Clearance',
            'description' => 'Desc',
            'date_requested' => '2026-10-08',
        ]);

        $result = $this->statusService->updateStatus($sub->serviceRequest->id, 'Cancelled');

        $this->assertTrue($result->isSuccess());
        $this->assertEquals('Cancelled', $result->getServiceRequest()->status);
    }

    // Test 3: In Progress -> Completed
    public function test_03_in_progress_can_move_to_completed()
    {
        $resident = Resident::factory()->create(['status' => 'Active']);
        $sub = $this->submissionService->submit([
            'resident_id' => $resident->id,
            'service_type' => 'Clearance',
            'description' => 'Desc',
            'date_requested' => '2026-10-08',
        ]);

        $this->statusService->updateStatus($sub->serviceRequest->id, 'In Progress');
        $result = $this->statusService->updateStatus($sub->serviceRequest->id, 'Completed');

        $this->assertTrue($result->isSuccess());
        $this->assertEquals('Completed', $result->getServiceRequest()->status);
    }

    // Test 4: In Progress -> Cancelled
    public function test_04_in_progress_can_move_to_cancelled()
    {
        $resident = Resident::factory()->create(['status' => 'Active']);
        $sub = $this->submissionService->submit([
            'resident_id' => $resident->id,
            'service_type' => 'Clearance',
            'description' => 'Desc',
            'date_requested' => '2026-10-08',
        ]);

        $this->statusService->updateStatus($sub->serviceRequest->id, 'In Progress');
        $result = $this->statusService->updateStatus($sub->serviceRequest->id, 'Cancelled');

        $this->assertTrue($result->isSuccess());
        $this->assertEquals('Cancelled', $result->getServiceRequest()->status);
    }

    // Test 5: Pending -> Completed (Invalid Direct Jump)
    public function test_05_pending_cannot_move_directly_to_completed()
    {
        $resident = Resident::factory()->create(['status' => 'Active']);
        $sub = $this->submissionService->submit([
            'resident_id' => $resident->id,
            'service_type' => 'Clearance',
            'description' => 'Desc',
            'date_requested' => '2026-10-08',
        ]);

        $result = $this->statusService->updateStatus($sub->serviceRequest->id, 'Completed');

        $this->assertTrue($result->isInvalidTransition());
        $this->assertEquals('Pending', ServiceRequest::find($sub->serviceRequest->id)->status);
    }

    // Test 6: In Progress -> Pending (Invalid Reverse)
    public function test_06_in_progress_cannot_return_to_pending()
    {
        $resident = Resident::factory()->create(['status' => 'Active']);
        $sub = $this->submissionService->submit([
            'resident_id' => $resident->id,
            'service_type' => 'Clearance',
            'description' => 'Desc',
            'date_requested' => '2026-10-08',
        ]);

        $this->statusService->updateStatus($sub->serviceRequest->id, 'In Progress');
        $result = $this->statusService->updateStatus($sub->serviceRequest->id, 'Pending');

        $this->assertTrue($result->isInvalidTransition());
        $this->assertEquals('In Progress', ServiceRequest::find($sub->serviceRequest->id)->status);
    }

    // Test 7: Completed is Terminal
    public function test_07_completed_is_terminal()
    {
        $resident = Resident::factory()->create(['status' => 'Active']);
        $sub = $this->submissionService->submit([
            'resident_id' => $resident->id,
            'service_type' => 'Clearance',
            'description' => 'Desc',
            'date_requested' => '2026-10-08',
        ]);

        $this->statusService->updateStatus($sub->serviceRequest->id, 'In Progress');
        $this->statusService->updateStatus($sub->serviceRequest->id, 'Completed');

        $result = $this->statusService->updateStatus($sub->serviceRequest->id, 'In Progress');

        $this->assertTrue($result->isInvalidTransition());
        $this->assertEquals('Completed', ServiceRequest::find($sub->serviceRequest->id)->status);
    }

    // Test 8: Cancelled is Terminal
    public function test_08_cancelled_is_terminal()
    {
        $resident = Resident::factory()->create(['status' => 'Active']);
        $sub = $this->submissionService->submit([
            'resident_id' => $resident->id,
            'service_type' => 'Clearance',
            'description' => 'Desc',
            'date_requested' => '2026-10-08',
        ]);

        $this->statusService->updateStatus($sub->serviceRequest->id, 'Cancelled');
        $result = $this->statusService->updateStatus($sub->serviceRequest->id, 'Pending');

        $this->assertTrue($result->isInvalidTransition());
        $this->assertEquals('Cancelled', ServiceRequest::find($sub->serviceRequest->id)->status);
    }

    // Test 9: Unsupported Status Rejected
    public function test_09_unsupported_status_is_rejected()
    {
        $resident = Resident::factory()->create(['status' => 'Active']);
        $sub = $this->submissionService->submit([
            'resident_id' => $resident->id,
            'service_type' => 'Clearance',
            'description' => 'Desc',
            'date_requested' => '2026-10-08',
        ]);

        $result = $this->statusService->updateStatus($sub->serviceRequest->id, 'Approved');

        $this->assertTrue($result->isUnsupportedStatus());
        $this->assertEquals('Pending', ServiceRequest::find($sub->serviceRequest->id)->status);
    }

    // Test 10: Nonexistent Service Request Handled Safely
    public function test_10_nonexistent_service_request_handled_safely()
    {
        $result = $this->statusService->updateStatus(99999, 'In Progress');

        $this->assertTrue($result->isNotFound());
    }

    // Test 11: Successful Transition Preserves Non-Status Attributes
    public function test_11_successful_transition_preserves_service_request_information()
    {
        $resident = Resident::factory()->create(['status' => 'Active']);
        $sub = $this->submissionService->submit([
            'resident_id' => $resident->id,
            'service_type' => 'Barangay Clearance',
            'description' => 'Employment requirement',
            'date_requested' => '2026-10-08',
        ]);

        $this->statusService->updateStatus($sub->serviceRequest->id, 'In Progress');
        $updated = ServiceRequest::find($sub->serviceRequest->id);

        $this->assertEquals($sub->serviceRequest->id, $updated->id);
        $this->assertEquals($resident->id, $updated->resident_id);
        $this->assertEquals('Barangay Clearance', $updated->service_type);
        $this->assertEquals('Employment requirement', $updated->description);
        $this->assertEquals('2026-10-08', $updated->date_requested);
        $this->assertEquals('In Progress', $updated->status);
    }

    // Test 12: Invalid Transition Does Not Modify Persistence
    public function test_12_invalid_transition_does_not_modify_persistence()
    {
        $resident = Resident::factory()->create(['status' => 'Active']);
        $sub = $this->submissionService->submit([
            'resident_id' => $resident->id,
            'service_type' => 'Clearance',
            'description' => 'Desc',
            'date_requested' => '2026-10-08',
        ]);

        $this->statusService->updateStatus($sub->serviceRequest->id, 'Completed');
        $this->assertEquals('Pending', ServiceRequest::find($sub->serviceRequest->id)->status);
    }

    // Test 13: Same-Status Request Is Rejected
    public function test_13_same_status_request_is_rejected()
    {
        $resident = Resident::factory()->create(['status' => 'Active']);
        $sub = $this->submissionService->submit([
            'resident_id' => $resident->id,
            'service_type' => 'Clearance',
            'description' => 'Desc',
            'date_requested' => '2026-10-08',
        ]);

        $result = $this->statusService->updateStatus($sub->serviceRequest->id, 'Pending');

        $this->assertTrue($result->isInvalidTransition());
        $this->assertEquals('Pending', ServiceRequest::find($sub->serviceRequest->id)->status);
    }

    // Test 14: Student-Designed Test - Full Sequential Lifecycle Journey
    public function test_14_student_designed_sequential_lifecycle_journey()
    {
        $resident = Resident::factory()->create(['status' => 'Active']);
        $sub = $this->submissionService->submit([
            'resident_id' => $resident->id,
            'service_type' => 'Permit',
            'description' => 'Business Permit',
            'date_requested' => '2026-10-08',
        ]);

        // Step 1: Pending -> In Progress
        $step1 = $this->statusService->updateStatus($sub->serviceRequest->id, 'In Progress');
        $this->assertTrue($step1->isSuccess());

        // Step 2: In Progress -> Completed
        $step2 = $this->statusService->updateStatus($sub->serviceRequest->id, 'Completed');
        $this->assertTrue($step2->isSuccess());

        // Step 3: Verify terminal protection from Completed -> Cancelled
        $step3 = $this->statusService->updateStatus($sub->serviceRequest->id, 'Cancelled');
        $this->assertTrue($step3->isInvalidTransition());
        $this->assertEquals('Completed', ServiceRequest::find($sub->serviceRequest->id)->status);
    }
}