<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Resident;
use App\Services\ResidentDeactivationService;
use App\Repositories\ResidentRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ResidentDeactivationTest extends TestCase
{
    use RefreshDatabase;

    protected ResidentDeactivationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ResidentDeactivationService(new ResidentRepository());
    }

    // Test 1 & 2: Active Resident can be deactivated and status updated in DB
    public function test_01_active_resident_can_be_deactivated_and_status_becomes_inactive()
    {
        $resident = Resident::factory()->create(['status' => 'Active']);

        $result = $this->service->deactivate($resident->id);

        $this->assertTrue($result->isSuccess());
        $this->assertDatabaseHas('residents', [
            'id' => $resident->id,
            'status' => 'Inactive',
        ]);
    }

    // Test 3 & 4: Resident ID and personal information are preserved
    public function test_02_deactivation_preserves_id_and_all_personal_information()
    {
        $resident = Resident::factory()->create([
            'first_name' => 'Juan',
            'last_name' => 'Cruz',
            'address' => '123 Main St',
            'contact_number' => '09171234567',
            'email' => 'juan@example.com',
            'status' => 'Active',
        ]);

        $this->service->deactivate($resident->id);

        $this->assertDatabaseHas('residents', [
            'id' => $resident->id,
            'first_name' => 'Juan',
            'last_name' => 'Cruz',
            'address' => '123 Main St',
            'contact_number' => '09171234567',
            'email' => 'juan@example.com',
            'status' => 'Inactive',
        ]);
    }

    // Test 5: Deactivated Resident remains retrievable
    public function test_03_deactivated_resident_remains_persisted_and_retrievable_by_id()
    {
        $resident = Resident::factory()->create(['status' => 'Active']);
        $this->service->deactivate($resident->id);

        $retrieved = Resident::find($resident->id);
        $this->assertNotNull($retrieved);
        $this->assertEquals('Inactive', $retrieved->status);
    }

    // Test 7: Already-Inactive Resident handled safely
    public function test_04_already_inactive_resident_is_handled_safely()
    {
        $resident = Resident::factory()->create(['status' => 'Inactive']);

        $result = $this->service->deactivate($resident->id);

        $this->assertEquals('already_inactive', $result->status);
        $this->assertEquals('Inactive', $resident->fresh()->status);
    }

    // Test 8 & 9: Nonexistent Resident handled safely without creating/deleting records
    public function test_05_nonexistent_resident_returns_not_found_and_does_not_modify_db()
    {
        $initialCount = Resident::count();

        $result = $this->service->deactivate(999999);

        $this->assertEquals('not_found', $result->status);
        $this->assertEquals($initialCount, Resident::count());
    }

    // Test 10: Deactivating one Resident does not affect another
    public function test_06_deactivating_one_resident_does_not_affect_another()
    {
        $res1 = Resident::factory()->create(['status' => 'Active']);
        $res2 = Resident::factory()->create(['status' => 'Active']);

        $this->service->deactivate($res1->id);

        $this->assertEquals('Inactive', $res1->fresh()->status);
        $this->assertEquals('Active', $res2->fresh()->status);
    }
}