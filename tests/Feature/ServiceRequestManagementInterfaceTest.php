<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Resident;
use App\Repositories\ServiceRequestRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class ServiceRequestManagementInterfaceTest extends TestCase
{
    use RefreshDatabase;

    private function createRequest(array $overrides = []): array
    {
        $resident = Resident::factory()->create([
            'status' => 'Active',
        ]);

        $data = array_merge([
            'resident_id' => $resident->id,
            'service_type' => 'Barangay Clearance',
            'description' => 'Request for employment purposes.',
            'date_requested' => '2026-11-13',
            'status' => 'Pending',
        ], $overrides);

        $request = (new ServiceRequestRepository())->save($data);

        return [
            'resident' => $resident,
            'request' => $request,
        ];
    }

    public function test_01_service_request_listing_page_loads()
    {
        $this->get(route('service-requests.index'))
            ->assertOk()
            ->assertSee('Service Request Management');
    }

    public function test_02_persisted_requests_are_displayed()
    {
        $data = $this->createRequest();

        $this->get(route('service-requests.index'))
            ->assertOk()
            ->assertSee('Barangay Clearance')
            ->assertSee('Pending');
    }

    public function test_03_empty_listing_displays_empty_message()
    {
        $this->get(route('service-requests.index'))
            ->assertOk()
            ->assertSee('No Service Requests found.');
    }

    public function test_04_requests_follow_date_and_id_descending_order()
    {
        $older = $this->createRequest([
            'date_requested' => '2026-11-10',
        ]);

        $sameDateFirst = $this->createRequest([
            'date_requested' => '2026-11-13',
        ]);

        $newest = $this->createRequest([
            'date_requested' => '2026-11-13',
        ]);

        $this->get(route('service-requests.index'))
            ->assertOk()
            ->assertSeeInOrder([
                (string) $newest['request']->id,
                (string) $sameDateFirst['request']->id,
                (string) $older['request']->id,
            ]);
    }

    public function test_05_search_finds_service_type_partially_and_case_insensitively()
    {
        $this->createRequest([
            'service_type' => 'Barangay Clearance',
        ]);

        $this->createRequest([
            'service_type' => 'Business Permit',
        ]);

        $this->get(route('service-requests.index', [
            'search' => 'CLEAR',
        ]))
            ->assertOk()
            ->assertSee('Barangay Clearance')
            ->assertDontSee('Business Permit');
    }

    public function test_06_search_finds_description_partially_and_case_insensitively()
    {
        $this->createRequest([
            'description' => 'Needed for EMPLOYMENT purposes.',
        ]);

        $this->createRequest([
            'description' => 'Needed for business registration.',
        ]);

        $this->get(route('service-requests.index', [
            'search' => 'employment',
        ]))
            ->assertOk()
            ->assertSee('Barangay Clearance')
            ->assertDontSee('Business Permit');
    }

    public function test_07_blank_search_displays_all_requests()
    {
        $this->createRequest([
            'service_type' => 'Barangay Clearance',
        ]);

        $this->createRequest([
            'service_type' => 'Business Permit',
        ]);

        $this->get(route('service-requests.index', [
            'search' => '   ',
        ]))
            ->assertOk()
            ->assertSee('Barangay Clearance')
            ->assertSee('Business Permit');
    }

   public function test_08_status_filter_displays_only_matching_requests()
{
    $this->createRequest([
        'service_type' => 'Pending Clearance',
        'status' => 'Pending',
    ]);

    $this->createRequest([
        'service_type' => 'Completed Permit',
        'status' => 'Completed',
    ]);

    $this->get(route('service-requests.index', [
        'status' => 'Completed',
    ]))
        ->assertOk()
        ->assertSee('Completed Permit')
        ->assertDontSee('Pending Clearance');
}

   public function test_09_search_and_status_filters_work_together()
{
    $this->createRequest([
        'service_type' => 'Pending Clearance',
        'status' => 'Pending',
    ]);

    $this->createRequest([
        'service_type' => 'Completed Clearance',
        'status' => 'Completed',
    ]);

    $this->createRequest([
        'service_type' => 'Completed Business Permit',
        'status' => 'Completed',
    ]);

    $this->get(route('service-requests.index', [
        'search' => 'Clearance',
        'status' => 'Completed',
    ]))
        ->assertOk()
        ->assertSee('Completed Clearance')
        ->assertDontSee('Pending Clearance')
        ->assertDontSee('Completed Business Permit');
}

   public function test_10_unsupported_status_filter_displays_an_error()
{
    $this->get(route('service-requests.index', [
        'status' => 'Archived',
    ]))
        ->assertOk()
        ->assertSee("Status 'Archived' is unsupported.");
}

    public function test_11_submission_form_loads_without_id_or_status_fields()
    {
        $this->get(route('service-requests.create'))
            ->assertOk()
            ->assertSee('Submit New Service Request')
            ->assertSee('resident_id')
            ->assertSee('service_type')
            ->assertSee('description')
            ->assertSee('date_requested')
            ->assertDontSee('name="id"', false)
            ->assertDontSee('name="status"', false);
    }

    public function test_12_valid_submission_saves_request_with_pending_status()
    {
        $resident = Resident::factory()->create([
            'status' => 'Active',
        ]);

        $response = $this->post(route('service-requests.store'), [
            'resident_id' => $resident->id,
            'service_type' => 'Barangay Clearance',
            'description' => 'Employment requirement.',
            'date_requested' => '2026-11-13',
        ]);

        $response->assertRedirect(route('service-requests.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('service_requests', [
            'resident_id' => $resident->id,
            'service_type' => 'Barangay Clearance',
            'description' => 'Employment requirement.',
            'date_requested' => '2026-11-13',
            'status' => 'Pending',
        ]);
    }

    public function test_13_invalid_submission_does_not_persist()
    {
        $resident = Resident::factory()->create([
            'status' => 'Active',
        ]);

        $response = $this->from(route('service-requests.create'))
            ->post(route('service-requests.store'), [
                'resident_id' => $resident->id,
                'service_type' => '',
                'description' => '',
                'date_requested' => 'invalid-date',
            ]);

        $response->assertRedirect(route('service-requests.create'))
            ->assertSessionHasErrors([
                'service_type',
                'description',
                'date_requested',
            ]);

        $this->assertDatabaseCount('service_requests', 0);
    }

    public function test_14_nonexistent_resident_cannot_submit_request()
    {
        $response = $this->from(route('service-requests.create'))
            ->post(route('service-requests.store'), [
                'resident_id' => 999999,
                'service_type' => 'Barangay Clearance',
                'description' => 'Employment requirement.',
                'date_requested' => '2026-11-13',
            ]);

        $response->assertRedirect(route('service-requests.create'))
            ->assertSessionHasErrors();

        $this->assertDatabaseCount('service_requests', 0);
    }

    public function test_15_inactive_resident_cannot_submit_request()
    {
        $resident = Resident::factory()->create([
            'status' => 'Inactive',
        ]);

        $response = $this->from(route('service-requests.create'))
            ->post(route('service-requests.store'), [
                'resident_id' => $resident->id,
                'service_type' => 'Barangay Clearance',
                'description' => 'Employment requirement.',
                'date_requested' => '2026-11-13',
            ]);

        $response->assertRedirect(route('service-requests.create'))
            ->assertSessionHasErrors();

        $this->assertDatabaseCount('service_requests', 0);
    }

    public function test_16_details_page_displays_existing_request()
    {
        $data = $this->createRequest();

        $this->get(route('service-requests.show', $data['request']->id))
            ->assertOk()
            ->assertSee('Service Request Details')
            ->assertSee('Barangay Clearance')
            ->assertSee('Request for employment purposes.')
            ->assertSee('Pending');
    }

    public function test_17_missing_service_request_details_return_404()
    {
        $this->get(route('service-requests.show', 999999))
            ->assertNotFound();
    }

    public function test_18_pending_request_can_start_processing()
    {
        $data = $this->createRequest();

        $this->post(
            route('service-requests.status', $data['request']->id),
            ['status' => 'In Progress']
        )
            ->assertRedirect(
                route('service-requests.show', $data['request']->id)
            )
            ->assertSessionHas('success');

        $this->assertDatabaseHas('service_requests', [
            'id' => $data['request']->id,
            'status' => 'In Progress',
        ]);
    }

    public function test_19_in_progress_request_can_be_completed()
    {
        $data = $this->createRequest([
            'status' => 'In Progress',
        ]);

        $this->post(
            route('service-requests.status', $data['request']->id),
            ['status' => 'Completed']
        )->assertRedirect(
            route('service-requests.show', $data['request']->id)
        );

        $this->assertDatabaseHas('service_requests', [
            'id' => $data['request']->id,
            'status' => 'Completed',
        ]);
    }

    public function test_20_pending_request_can_be_cancelled()
    {
        $data = $this->createRequest();

        $this->post(
            route('service-requests.status', $data['request']->id),
            ['status' => 'Cancelled']
        )->assertRedirect(
            route('service-requests.show', $data['request']->id)
        );

        $this->assertDatabaseHas('service_requests', [
            'id' => $data['request']->id,
            'status' => 'Cancelled',
        ]);
    }

    public function test_21_in_progress_request_can_be_cancelled()
    {
        $data = $this->createRequest([
            'status' => 'In Progress',
        ]);

        $this->post(
            route('service-requests.status', $data['request']->id),
            ['status' => 'Cancelled']
        )->assertRedirect(
            route('service-requests.show', $data['request']->id)
        );

        $this->assertDatabaseHas('service_requests', [
            'id' => $data['request']->id,
            'status' => 'Cancelled',
        ]);
    }

    public function test_22_invalid_direct_transition_is_rejected()
    {
        $data = $this->createRequest();

        $this->from(route('service-requests.show', $data['request']->id))
            ->post(
                route('service-requests.status', $data['request']->id),
                ['status' => 'Completed']
            )
            ->assertRedirect(
                route('service-requests.show', $data['request']->id)
            )
            ->assertSessionHasErrors();

        $this->assertDatabaseHas('service_requests', [
            'id' => $data['request']->id,
            'status' => 'Pending',
        ]);
    }

    public function test_23_completed_and_cancelled_requests_are_terminal()
    {
        $completed = $this->createRequest([
            'status' => 'Completed',
        ]);

        $cancelled = $this->createRequest([
            'status' => 'Cancelled',
        ]);

        $this->get(route('service-requests.show', $completed['request']->id))
            ->assertOk()
            ->assertDontSee('Start Processing')
            ->assertDontSee('Mark as Completed')
            ->assertDontSee('Cancel Request');

        $this->get(route('service-requests.show', $cancelled['request']->id))
            ->assertOk()
            ->assertDontSee('Start Processing')
            ->assertDontSee('Mark as Completed')
            ->assertDontSee('Cancel Request');

        $this->assertDatabaseHas('service_requests', [
            'id' => $completed['request']->id,
            'status' => 'Completed',
        ]);

        $this->assertDatabaseHas('service_requests', [
            'id' => $cancelled['request']->id,
            'status' => 'Cancelled',
        ]);
    }

    public function test_24_get_requests_do_not_change_service_request_status()
    {
        $data = $this->createRequest();

        $this->get(route('service-requests.index'))->assertOk();

        $this->get(route('service-requests.show', $data['request']->id))
            ->assertOk();

        $this->assertDatabaseHas('service_requests', [
            'id' => $data['request']->id,
            'status' => 'Pending',
        ]);

        $this->assertDatabaseCount('service_requests', 1);
    }

    public function test_25_status_update_route_does_not_accept_get_requests()
    {
        $data = $this->createRequest();

        $this->get(
            "/service-requests/{$data['request']->id}/status"
        )->assertMethodNotAllowed();

        $this->assertDatabaseHas('service_requests', [
            'id' => $data['request']->id,
            'status' => 'Pending',
        ]);
    }
}
