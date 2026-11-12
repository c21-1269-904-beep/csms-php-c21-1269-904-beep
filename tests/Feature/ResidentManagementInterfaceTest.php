<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Resident;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ResidentManagementInterfaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_01_resident_listing_page_loads()
    {
        $this->get(route('residents.index'))
            ->assertOk()
            ->assertSee('Resident Management');
    }

    public function test_02_persisted_residents_are_displayed()
    {
        Resident::factory()->create([
            'first_name' => 'Juan',
            'last_name' => 'Cruz',
        ]);

        $this->get(route('residents.index'))
            ->assertOk()
            ->assertSee('Juan')
            ->assertSee('Cruz');
    }

    public function test_03_search_finds_resident_by_first_name()
    {
        Resident::factory()->create([
            'first_name' => 'Maria',
            'last_name' => 'Santos',
        ]);

        $this->get(route('residents.index', ['search' => 'maria']))
            ->assertOk()
            ->assertSee('Maria')
            ->assertSee('Santos');
    }

    public function test_04_search_finds_resident_by_last_name()
    {
        Resident::factory()->create([
            'first_name' => 'Pedro',
            'last_name' => 'Hernandez',
        ]);

        $this->get(route('residents.index', ['search' => 'HERNANDEZ']))
            ->assertOk()
            ->assertSee('Pedro')
            ->assertSee('Hernandez');
    }

    public function test_05_blank_search_returns_all_residents_in_order()
    {
        Resident::factory()->create([
            'first_name' => 'Zara',
            'last_name' => 'Santos',
        ]);

        Resident::factory()->create([
            'first_name' => 'Ana',
            'last_name' => 'Cruz',
        ]);

        $this->get(route('residents.index', ['search' => '   ']))
            ->assertOk()
            ->assertSeeInOrder(['Cruz', 'Santos']);
    }

    public function test_06_registration_form_loads()
    {
        $this->get(route('residents.create'))
            ->assertOk()
            ->assertSee('Register New Resident')
            ->assertSee('first_name')
            ->assertSee('last_name');
    }

    public function test_07_valid_registration_saves_active_resident()
    {
        $response = $this->post(route('residents.store'), [
            'first_name' => 'Maria',
            'last_name' => 'Santos',
            'address' => 'Manila',
            'contact_number' => '09171234567',
            'email' => 'maria@example.com',
        ]);

        $response->assertRedirect(route('residents.index'));

        $this->assertDatabaseHas('residents', [
            'first_name' => 'Maria',
            'last_name' => 'Santos',
            'status' => 'Active',
        ]);

        $this->assertNotNull(
            Resident::where('email', 'maria@example.com')->first()->id
        );
    }

    public function test_08_invalid_registration_does_not_persist()
    {
        $response = $this->from(route('residents.create'))
            ->post(route('residents.store'), [
                'first_name' => '',
                'last_name' => 'Santos',
                'address' => 'Manila',
                'contact_number' => 'INVALID',
                'email' => 'invalid-email',
            ]);

        $response->assertRedirect(route('residents.create'))
            ->assertSessionHasErrors([
                'first_name',
                'contact_number',
                'email',
            ]);

        $this->assertDatabaseCount('residents', 0);
    }

    public function test_09_edit_form_displays_existing_values_and_read_only_fields()
    {
        $resident = Resident::factory()->create([
            'first_name' => 'Juan',
            'last_name' => 'Cruz',
            'status' => 'Inactive',
        ]);

        $this->get(route('residents.edit', $resident->id))
            ->assertOk()
            ->assertSee('Juan')
            ->assertSee('Cruz')
            ->assertSee('Inactive')
            ->assertSee('readonly');
    }

    public function test_10_valid_update_preserves_id_and_status()
    {
        $resident = Resident::factory()->create([
            'status' => 'Inactive',
        ]);

        $originalId = $resident->id;

        $response = $this->put(
            route('residents.update', $resident->id),
            [
                'first_name' => 'Updated',
                'last_name' => 'Resident',
                'address' => 'New Address',
                'contact_number' => '09181234567',
                'email' => 'updated@example.com',
            ]
        );

        $response->assertRedirect(route('residents.index'));

        $this->assertDatabaseHas('residents', [
            'id' => $originalId,
            'first_name' => 'Updated',
            'last_name' => 'Resident',
            'status' => 'Inactive',
        ]);
    }

    public function test_11_invalid_update_does_not_modify_database()
    {
        $resident = Resident::factory()->create([
            'first_name' => 'Original',
            'status' => 'Active',
        ]);

        $response = $this->from(
            route('residents.edit', $resident->id)
        )->put(route('residents.update', $resident->id), [
            'first_name' => '',
            'last_name' => 'Changed',
            'address' => 'Changed Address',
            'contact_number' => 'INVALID',
            'email' => 'invalid-email',
        ]);

        $response->assertRedirect(
            route('residents.edit', $resident->id)
        )->assertSessionHasErrors();

        $this->assertDatabaseHas('residents', [
            'id' => $resident->id,
            'first_name' => 'Original',
            'status' => 'Active',
        ]);
    }

    public function test_12_missing_resident_edit_returns_404()
    {
        $this->get(route('residents.edit', 999999))
            ->assertNotFound();
    }

    public function test_13_deactivation_changes_status_without_deleting_resident()
    {
        $resident = Resident::factory()->create([
            'status' => 'Active',
        ]);

        $response = $this->patch(
            route('residents.deactivate', $resident->id)
        );

        $response->assertRedirect(route('residents.index'));

        $this->assertDatabaseHas('residents', [
            'id' => $resident->id,
            'status' => 'Inactive',
        ]);

        $this->assertDatabaseCount('residents', 1);
    }

    public function test_14_inactive_resident_remains_visible_without_deactivate_button()
    {
        $resident = Resident::factory()->create([
            'first_name' => 'InactiveUser',
            'status' => 'Inactive',
        ]);

        $response = $this->get(route('residents.index'));

        $response->assertOk()
            ->assertSee('InactiveUser')
            ->assertSee('Inactive')
            ->assertDontSee(
                route('residents.deactivate', $resident->id)
            );
    }

    public function test_15_get_requests_do_not_deactivate_residents()
    {
        $resident = Resident::factory()->create([
            'status' => 'Active',
        ]);

        $this->get(route('residents.index'))->assertOk();

        $this->get(route('residents.edit', $resident->id))
            ->assertOk();

        $this->get(
            "/residents/{$resident->id}/deactivate"
        )->assertMethodNotAllowed();

        $this->assertDatabaseHas('residents', [
            'id' => $resident->id,
            'status' => 'Active',
        ]);
    }
}
