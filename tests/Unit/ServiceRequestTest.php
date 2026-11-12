
<?php

namespace Tests\Unit;

use App\Models\ServiceRequest;
use PHPUnit\Framework\TestCase;

class ServiceRequestTest extends TestCase
{
    public function test_service_request_can_be_created(): void
    {
        $request = new ServiceRequest(
            25,
            'Barangay Clearance',
            'Request for employment requirement',
            '2026-11-12'
        );

        $this->assertInstanceOf(ServiceRequest::class, $request);
    }

    public function test_service_request_information_is_accessible(): void
    {
        $request = new ServiceRequest(
            25,
            'Barangay Clearance',
            'Request for employment requirement',
            '2026-11-12'
        );

        $this->assertEquals(25, $request->resident_id);
        $this->assertEquals('Barangay Clearance', $request->service_type);
        $this->assertEquals(
            'Request for employment requirement',
            $request->description
        );
        $this->assertEquals('2026-11-12', $request->date_requested);
    }

    public function test_resident_id_is_preserved(): void
    {
        $request = new ServiceRequest(
            25,
            'Certificate Request',
            'Request for certificate',
            '2026-11-12'
        );

        $this->assertEquals(25, $request->resident_id);
    }

    public function test_new_service_request_has_unassigned_id(): void
    {
        $request = new ServiceRequest(
            25,
            'Barangay Clearance',
            'Employment requirement',
            '2026-11-12'
        );

        $this->assertNull($request->id);
    }

    public function test_new_service_request_defaults_to_pending(): void
    {
        $request = new ServiceRequest(
            25,
            'Barangay Clearance',
            'Employment requirement',
            '2026-11-12'
        );

        $this->assertEquals('Pending', $request->status);
    }

    public function test_service_request_objects_have_independent_information(): void
    {
        $firstRequest = new ServiceRequest(
            25,
            'Barangay Clearance',
            'Employment requirement',
            '2026-11-12'
        );

        $secondRequest = new ServiceRequest(
            30,
            'Community Assistance',
            'Medical assistance request',
            '2026-11-13'
        );

        $this->assertEquals(25, $firstRequest->resident_id);
        $this->assertEquals(30, $secondRequest->resident_id);

        $this->assertEquals('Barangay Clearance', $firstRequest->service_type);
        $this->assertEquals('Community Assistance', $secondRequest->service_type);

        $this->assertNotSame($firstRequest, $secondRequest);
    }
}
