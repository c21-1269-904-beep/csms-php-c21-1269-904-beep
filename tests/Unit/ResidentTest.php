<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Resident;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ResidentTest extends TestCase
{
    use RefreshDatabase;

    public function test_resident_creation_access_and_status()
    {
        $resident = Resident::factory()->create();

        $this->assertNotNull($resident->id);
        $this->assertEquals('Active', $resident->status);
    }
}