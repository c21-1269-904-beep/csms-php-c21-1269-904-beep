<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Resident;
use App\Services\ServiceRequestSubmissionService;
use App\Repositories\ServiceRequestRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

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

        $result = $this->service->submit([
            'resident_id' => $resident->id,
            'service_type' => 'Barangay Clearance',
            'description' => 'For employment purposes.',
            'date_requested' => '2026-10-07',
        ]);

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
        $this->assertEquals($resident->id, $retrieved->resident_id);
        $this->assertEquals('Permit Request', $retrieved->service_type);
        $this->assertEquals('Business permit renewal.', $retrieved->description);
        $this->assertEquals('2026-10-07', $retrieved->date_requested);
        $this->assertEquals('Pending', $retrieved->status);
    }

    public function test_03_blank_service_type_and_description_fail_validation()
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
        $this->assertEquals(0, DB::table('service_requests')->count());
    }

    public function test_04_nonexistent_resident_prevents_submission()
    {
        $result = $this->service->submit([
            'resident_id' => 99999,
            'service_type' => 'Clearance',
            'description' => 'Test description',
            'date_requested' => '2026-10-07',
        ]);

        $this->assertEquals('resident_not_found', $result->status);
        $this->assertEquals(0, DB::table('service_requests')->count());
    }

    public function test_05_inactive_resident_cannot_submit_service_request()
    {
        $resident = Resident::factory()->create(['status' => 'Inactive']);

        $result = $this->service->submit([
            'resident_id' => $resident->id,
            'service_type' => 'Clearance',
            'description' => 'Test description',
            'date_requested' => '2026-10-07',
        ]);

        $this->assertEquals('resident_inactive', $result->status);
        $this->assertEquals(0, DB::table('service_requests')->count());
    }

    public function test_06_non_pending_initial_status_is_rejected()
    {
        $resident = Resident::factory()->create(['status' => 'Active']);

        $result = $this->service->submit([
            'resident_id' => $resident->id,
            'service_type' => 'Clearance',
            'description' => 'Test description',
            'date_requested' => '2026-10-07',
            'status' => 'Completed',
        ]);

        $this->assertEquals('validation_failed', $result->status);
        $this->assertArrayHasKey('status', $result->errors);
        $this->assertEquals(0, DB::table('service_requests')->count());
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

    public function test_08_impossible_calendar_date_is_rejected()
    {
        $resident = Resident::factory()->create(['status' => 'Active']);

        $result = $this->service->submit([
            'resident_id' => $resident->id,
            'service_type' => 'Clearance',
            'description' => 'Date validation test',
            'date_requested' => '2026-02-30',
        ]);

        $this->assertEquals('validation_failed', $result->status);
        $this->assertArrayHasKey('date_requested', $result->errors);
        $this->assertEquals(0, DB::table('service_requests')->count());
    }

    public function test_09_invalid_date_format_is_rejected()
    {
        $resident = Resident::factory()->create(['status' => 'Active']);

        $result = $this->service->submit([
            'resident_id' => $resident->id,
            'service_type' => 'Clearance',
            'description' => 'Date format test',
            'date_requested' => '07/10/2026',
        ]);

        $this->assertEquals('validation_failed', $result->status);
        $this->assertArrayHasKey('date_requested', $result->errors);
        $this->assertEquals(0, DB::table('service_requests')->count());
    }

    public function test_10_preassigned_service_request_id_is_rejected()
    {
        $resident = Resident::factory()->create(['status' => 'Active']);

        $result = $this->service->submit([
            'id' => 100,
            'resident_id' => $resident->id,
            'service_type' => 'Clearance',
            'description' => 'ID validation test',
            'date_requested' => '2026-10-07',
        ]);

        $this->assertEquals('validation_failed', $result->status);
        $this->assertArrayHasKey('id', $result->errors);
        $this->assertEquals(0, DB::table('service_requests')->count());
    }

    public function test_11_invalid_resident_ids_are_rejected()
    {
        foreach ([0, -1, 1.5, '1.5'] as $invalidId) {
            $result = $this->service->submit([
                'resident_id' => $invalidId,
                'service_type' => 'Clearance',
                'description' => 'Resident ID validation test',
                'date_requested' => '2026-10-07',
            ]);

            $this->assertEquals('validation_failed', $result->status);
            $this->assertArrayHasKey('resident_id', $result->errors);
        }

        $this->assertEquals(0, DB::table('service_requests')->count());
    }

    public function test_12_whitespace_only_service_type_is_rejected()
    {
        $resident = Resident::factory()->create(['status' => 'Active']);

        $result = $this->service->submit([
            'resident_id' => $resident->id,
            'service_type' => '   ',
            'description' => 'Valid description',
            'date_requested' => '2026-10-07',
        ]);

        $this->assertEquals('validation_failed', $result->status);
        $this->assertArrayHasKey('service_type', $result->errors);
        $this->assertEquals(0, DB::table('service_requests')->count());
    }

    public function test_13_whitespace_only_description_is_rejected()
    {
        $resident = Resident::factory()->create(['status' => 'Active']);

        $result = $this->service->submit([
            'resident_id' => $resident->id,
            'service_type' => 'Clearance',
            'description' => '   ',
            'date_requested' => '2026-10-07',
        ]);

        $this->assertEquals('validation_failed', $result->status);
        $this->assertArrayHasKey('description', $result->errors);
        $this->assertEquals(0, DB::table('service_requests')->count());
    }

    public function test_14_null_service_request_id_is_accepted_as_unassigned()
    {
        $resident = Resident::factory()->create(['status' => 'Active']);

        $result = $this->service->submit([
            'id' => null,
            'resident_id' => $resident->id,
            'service_type' => 'Clearance',
            'description' => 'Unassigned ID test',
            'date_requested' => '2026-10-07',
        ]);

        $this->assertTrue($result->isSuccess());
        $this->assertNotNull($result->serviceRequest->id);
        $this->assertEquals(1, DB::table('service_requests')->count());
    }
}
