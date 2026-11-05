<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Resident;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ResidentPersistenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_resident_can_be_saved_to_database(): void
    {
        $resident = Resident::factory()->create([
            'first_name' => 'Juan',
            'last_name' => 'Cruz',
            'address' => '123 Main Street',
            'contact_number' => '09171234567',
            'email' => 'juan@example.com',
            'status' => 'Active',
        ]);

        $this->assertDatabaseHas('residents', [
            'id' => $resident->id,
            'first_name' => 'Juan',
            'last_name' => 'Cruz',
            'email' => 'juan@example.com',
            'status' => 'Active',
        ]);
    }

    public function test_resident_can_be_retrieved_from_database(): void
    {
        $resident = Resident::factory()->create();

        $savedResident = Resident::findOrFail($resident->id);

        $this->assertEquals($resident->id, $savedResident->id);
        $this->assertEquals($resident->first_name, $savedResident->first_name);
        $this->assertEquals($resident->last_name, $savedResident->last_name);
        $this->assertEquals($resident->email, $savedResident->email);
    }
}
