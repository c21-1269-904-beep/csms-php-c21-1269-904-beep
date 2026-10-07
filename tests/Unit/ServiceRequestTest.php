<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\ServiceRequest;

class ServiceRequestTest extends TestCase
{
    // Test 1 & 2: Service Request can be created and information is accessible
    public function test_01_service_request_can_be_created_and_information_is_accessible()
    {
        $data = [
            'resident_id' => 25,
            'service_type' => 'Barangay Clearance',
            'description' => 'Requesting clearance for employment requirements.',
            'date_requested' => '2026-10-07',
        ];

        $request = new ServiceRequest($data);

        $this->assertEquals(25, $request->resident_id);
        $this->assertEquals('Barangay Clearance', $request->service_type);
        $this->assertEquals('Requesting clearance for employment requirements.', $request->description);
        $this->assertEquals('2026-10-07', $request->date_requested);
    }

    // Test 3: Resident ID is preserved correctly
    public function test_02_resident_id_is_preserved()
    {
        $request = new ServiceRequest(['resident_id' => 99]);

        $this->assertEquals(99, $request->resident_id);
    }

    // Test 4: New Service Request has an unassigned ID before persistence
    public function test_03_new_service_request_has_unassigned_id()
    {
        $request = new ServiceRequest([
            'resident_id' => 10,
            'service_type' => 'Permit Request',
        ]);

        $this->assertNull($request->id);
    }

    // Test 5: New Service Request defaults to Pending status
    public function test_04_new_service_request_defaults_to_pending()
    {
        $request = new ServiceRequest([
            'resident_id' => 15,
            'service_type' => 'Community Assistance',
            'description' => 'Medical financial aid',
            'date_requested' => '2026-10-07',
        ]);

        $this->assertEquals('Pending', $request->status);
    }

    // Test 6: Separate Service Request objects retain independent data
    public function test_05_multiple_service_request_objects_are_independent()
    {
        $req1 = new ServiceRequest([
            'resident_id' => 1,
            'service_type' => 'Type A',
            'description' => 'Desc A',
        ]);

        $req2 = new ServiceRequest([
            'resident_id' => 2,
            'service_type' => 'Type B',
            'description' => 'Desc B',
        ]);

        $this->assertEquals(1, $req1->resident_id);
        $this->assertEquals('Type A', $req1->service_type);

        $this->assertEquals(2, $req2->resident_id);
        $this->assertEquals('Type B', $req2->service_type);
    }
}