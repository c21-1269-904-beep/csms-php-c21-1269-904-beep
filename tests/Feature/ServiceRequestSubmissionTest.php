<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Resident;
use App\Models\ServiceRequest;
use App\Services\ServiceRequestSubmissionService;
use App\Repositories\ServiceRequestRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ServiceRequestSubmissionTest extends TestCase
{
    use RefreshDatabase;

    protected ServiceRequestSubmissionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ServiceRequestSubmissionService();
    }

    public function test_01_valid_service_request_submission_succeeds_and_generates_id()
    {
        $resident = Resident::factory()->create(['status' => 'Active']);

        $data = [
            'resident_id' => $resident->id,
            'service_type' => 'Barangay Clearance',
            'description' => 'For employment purposes.',
            'date_requested' => '2026-10-07',
        ];

        $result = $this->service->submit($data);

        $this->assertTrue($result->isSuccess());
        $this->assertNotNull($result->serviceRequest->id);
        $this->assertEquals('Pending', $result->serviceRequest->status);
    }

    public function test_02_submitted_service_request_is_persisted_and_retrievable()
    {
        $resident = Resident::factory()->create(['status' => 'Active']);
        $result = $this->service->submit([
            'resident_id' => $resident->id,
            'service_type' => 'Permit Request',
            'description' => 'Business permit renewal.',
            'date_requested' => '2026-10-07',
        ]);

        $repo = new ServiceRequestRepository();
        $retrieved = $repo->findById($result->serviceRequest->id);

        $this->assertNotNull($retrieved);
        $this->assertEquals('Permit Request', $retrieved->service_type);
    }

    public function test_03_blank_service_type_or_description_fails_validation()
    {
        $resident = Resident::factory()->create(['status' => 'Active']);

        $result = $this->service->submit([
            'resident_id' => $resident->id,
            'service_type' => ' ',
            'description' => '',
            'date_requested' => '2026-10-07',
        ]);

        $this->assertEquals('validation_failed', $result->status);
        $this->assertArrayHasKey('service_type', $result->errors);
        $this->assertArrayHasKey('description', $result->errors);
        $this->assertEquals(0, ServiceRequest::count());
    }

    public function test_04_nonexistent_resident_prevents_submission()
    {
        $result = $this->service->submit([
            'resident_id' => 99999,
            'service_type' => 'Clearance',
            'description' => 'Test desc',
            'date_requested' => '2026-10-07',
        ]);

        $this->assertEquals('resident_not_found', $result->status);
        $this->assertEquals(0, ServiceRequest::count());
    }

    public function test_05_inactive_resident_cannot_submit_service_request()
    {
        $resident = Resident::factory()->create(['status' => 'Inactive']);

        $result = $this->service->submit([
            'resident_id' => $resident->id,
            'service_type' => 'Clearance',
            'description' => 'Test desc',
            'date_requested' => '2026-10-07',
        ]);

        $this->assertEquals('resident_inactive', $result->status);
        $this->assertEquals(0, ServiceRequest::count());
    }

    public function test_06_non_pending_initial_status_is_rejected()
    {
        $resident = Resident::factory()->create(['status' => 'Active']);

        $result = $this->service->submit([
            'resident_id' => $resident->id,
            'service_type' => 'Clearance',
            'description' => 'Test desc',
            'date_requested' => '2026-10-07',
            'status' => 'Completed',
        ]);

        $this->assertEquals('validation_failed', $result->status);
        $this->assertArrayHasKey('status', $result->errors);
    }

    public function test_07_submission_does_not_modify_resident()
    {
        $resident = Resident::factory()->create([
            'first_name' => 'Maria',
            'status' => 'Active',
        ]);

        $this->service->submit([
            'resident_id' => $resident->id,
            'service_type' => 'Indigency Certificate',
            'description' => 'Aid requirement',
            'date_requested' => '2026-10-07',
        ]);

        $this->assertEquals('Maria', $resident->fresh()->first_name);
        $this->assertEquals('Active', $resident->fresh()->status);
    }
}