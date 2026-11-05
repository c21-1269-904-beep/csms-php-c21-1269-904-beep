<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Resident;
use App\Services\ResidentUpdateService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ResidentUpdateTest extends TestCase
{
    use RefreshDatabase;

    private ResidentUpdateService $updateService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->updateService = new ResidentUpdateService();
    }

    /** @test */
    public function test_1_valid_resident_update_succeeds()
    {
        $resident = Resident::factory()->create([
            'first_name' => 'Juan',
            'last_name'  => 'Cruz',
            'status'     => 'Active',
        ]);

        $result = $this->updateService->update($resident->id, [
            'first_name'     => 'Juan Miguel',
            'last_name'      => 'Dela Cruz',
            'address'        => '123 Main St',
            'contact_number' => '09171234567',
            'email'          => 'juan@example.com',
        ]);

        $this->assertTrue($result->isSuccess());
    }

    /** @test */
    public function test_2_resident_id_is_preserved()
    {
        $resident = Resident::factory()->create();
        $originalId = $resident->id;

        $result = $this->updateService->update($originalId, [
            'first_name'     => 'Updated',
            'last_name'      => 'Name',
            'address'        => 'Updated Address',
            'contact_number' => '09181234567',
            'email'          => 'updated@example.com',
        ]);

        $this->assertEquals($originalId, $result->getResident()->id);
    }

    /** @test */
    public function test_3_permitted_information_is_persisted()
    {
        $resident = Resident::factory()->create();

        $this->updateService->update($resident->id, [
            'first_name'     => 'Maria',
            'last_name'      => 'Santos',
            'address'        => '456 Oak St',
            'contact_number' => '09981234567',
            'email'          => 'maria@example.com',
        ]);

        $this->assertDatabaseHas('residents', [
            'id'             => $resident->id,
            'first_name'     => 'Maria',
            'last_name'      => 'Santos',
            'address'        => '456 Oak St',
            'contact_number' => '09981234567',
            'email'          => 'maria@example.com',
        ]);
    }

    /** @test */
    public function test_4_resident_status_is_preserved()
    {
        $active = Resident::factory()->create(['status' => 'Active']);
        $inactive = Resident::factory()->create(['status' => 'Inactive']);

        $this->updateService->update($active->id, [
            'first_name'     => 'ActiveUpdate',
            'last_name'      => 'Test',
            'address'        => 'Sample St',
            'contact_number' => '09171234567',
            'email'          => 'active@example.com',
        ]);

        $this->updateService->update($inactive->id, [
            'first_name'     => 'InactiveUpdate',
            'last_name'      => 'Test',
            'address'        => 'Sample St',
            'contact_number' => '09171234567',
            'email'          => 'inactive@example.com',
        ]);

        $this->assertEquals('Active', $active->fresh()->status);
        $this->assertEquals('Inactive', $inactive->fresh()->status);
    }

    /** @test */
    public function test_5_invalid_update_fails()
    {
        $resident = Resident::factory()->create();

        $result = $this->updateService->update($resident->id, [
            'first_name'     => '', // Invalid empty string
            'last_name'      => 'Cruz',
            'address'        => '123 Main St',
            'contact_number' => 'INVALID_NUMBER',
            'email'          => 'not-an-email',
        ]);

        $this->assertTrue($result->isValidationFailed());
    }

    /** @test */
    public function test_6_invalid_update_does_not_modify_stored_data()
    {
        $resident = Resident::factory()->create([
            'first_name'     => 'Juan',
            'contact_number' => '09171234567',
        ]);

        $this->updateService->update($resident->id, [
            'first_name'     => '',
            'contact_number' => 'INVALID',
            'address'        => 'Valid Address',
            'last_name'      => 'Valid Last',
            'email'          => 'valid@example.com',
        ]);

        $fresh = $resident->fresh();
        $this->assertEquals('Juan', $fresh->first_name);
        $this->assertEquals('09171234567', $fresh->contact_number);
    }

    /** @test */
    public function test_7_updating_nonexistent_resident_handled_safely()
    {
        $result = $this->updateService->update(999999, [
            'first_name'     => 'Ghost',
            'last_name'      => 'User',
            'address'        => 'Somewhere St',
            'contact_number' => '09171234567',
            'email'          => 'ghost@example.com',
        ]);

        $this->assertTrue($result->isNotFound());
    }

    /** @test */
    public function test_8_nonexistent_update_does_not_create_resident()
    {
        $initialCount = Resident::count();

        $this->updateService->update(999999, [
            'first_name'     => 'Ghost',
            'last_name'      => 'User',
            'address'        => 'Somewhere St',
            'contact_number' => '09171234567',
            'email'          => 'ghost@example.com',
        ]);

        $this->assertEquals($initialCount, Resident::count());
    }

    /** @test */
    public function test_9_updated_resident_is_visible_through_t05_querying()
    {
        $resident = Resident::factory()->create(['first_name' => 'OriginalName']);

        $this->updateService->update($resident->id, [
            'first_name'     => 'NewUniqueName',
            'last_name'      => $resident->last_name,
            'address'        => $resident->address,
            'contact_number' => $resident->contact_number,
            'email'          => $resident->email,
        ]);

        $found = Resident::where('first_name', 'NewUniqueName')->first();
        $this->assertNotNull($found);
        $this->assertEquals($resident->id, $found->id);
    }

    /** @test */
    public function test_10_contact_number_preserves_leading_zero()
    {
        $resident = Resident::factory()->create();

        $this->updateService->update($resident->id, [
            'first_name'     => $resident->first_name,
            'last_name'      => $resident->last_name,
            'address'        => $resident->address,
            'contact_number' => '09181234567',
            'email'          => $resident->email,
        ]);

        $fresh = $resident->fresh();
        $this->assertEquals('09181234567', $fresh->contact_number);
    }
}