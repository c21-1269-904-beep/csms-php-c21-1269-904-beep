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
        $this->statusService = new ServiceRequestStatusService();$this->submissionService = new ServiceRequestSubmissionService();
    }

    public function test_01_pending_can_move_to_in_progress()
    {
        $resident = Resident::factory()->create(['status' => 'Active']);
        $sub =$this->submissionService->submit([
            'resident_id' => $resident->id,
            'service_type' => 'Clearance',
            'description' => 'Desc',
            'date_requested' => '2026-10-08',
        ]);

        $result = $this->statusService->updateStatus($sub->serviceRequest->id, 'In Progress');

        $this->assertTrue($result->isSuccess());
        $this->assertEquals('In Progress',$result->getServiceRequest()->status);
        $this->assertEquals('In Progress', ServiceRequest::find($sub->serviceRequest->id)->status);
    }

    public function test_02_pending_can_move_to_cancelled()
    {
        $resident = Resident::factory()->create