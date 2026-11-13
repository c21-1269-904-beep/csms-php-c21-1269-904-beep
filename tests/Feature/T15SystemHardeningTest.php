<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Resident;
use App\Repositories\ServiceRequestRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class T15SystemHardeningTest extends TestCase
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

    public function test_01_malformed_status_input_is_rejected_without_changing_request()
    {
        $data = $this->createRequest();

        $response = $this->from(
            route('service-requests.show', $data['request']->id)
        )->post(
            route('service-requests.status', $data['request']->id),
            ['status' => ['Completed']]
        );

        $response->assertRedirect(
            route('service-requests.show', $data['request']->id)
        );

        $response->assertSessionHasErrors();

        $this->assertDatabaseHas('service_requests', [
            'id' => $data['request']->id,
            'resident_id' => $data['resident']->id,
            'service_type' => 'Barangay Clearance',
            'description' => 'Request for employment purposes.',
            'date_requested' => '2026-11-13',
            'status' => 'Pending',
        ]);
    }

    public function test_02_rejected_transition_preserves_all_service_request_fields()
    {
        $data = $this->createRequest();

        $before = (array) DB::table('service_requests')
            ->where('id', $data['request']->id)
            ->first();

        $response = $this->from(
            route('service-requests.show', $data['request']->id)
        )->post(
            route('service-requests.status', $data['request']->id),
            ['status' => 'Completed']
        );

        $response->assertRedirect(
            route('service-requests.show', $data['request']->id)
        );

        $after = (array) DB::table('service_requests')
            ->where('id', $data['request']->id)
            ->first();

        $this->assertNotEmpty($before);
        $this->assertNotEmpty($after);

        $this->assertSame(
            $before,
            $after,
            'A rejected status transition must not modify any service request field.'
        );
    }

    public function test_03_get_request_details_does_not_modify_request_or_resident()
    {
        $data = $this->createRequest();

        $requestBefore = (array) DB::table('service_requests')
            ->where('id', $data['request']->id)
            ->first();

        $residentBefore = (array) DB::table('residents')
            ->where('id', $data['resident']->id)
            ->first();

        $response = $this->get(
            route('service-requests.show', $data['request']->id)
        );

        $response->assertOk();

        $requestAfter = (array) DB::table('service_requests')
            ->where('id', $data['request']->id)
            ->first();

        $residentAfter = (array) DB::table('residents')
            ->where('id', $data['resident']->id)
            ->first();

        $this->assertSame($requestBefore, $requestAfter);
        $this->assertSame($residentBefore, $residentAfter);
    }
    public function test_04_deactivated_resident_cannot_submit_new_service_request()
{
    $resident = Resident::factory()->create([
        'status' => 'Active',
    ]);

    $this->patch(
        route('residents.deactivate', $resident->id)
    );

    $resident->refresh();

    $this->assertSame('Inactive', $resident->status);

    $response = $this->post(route('service-requests.store'), [
        'resident_id' => $resident->id,
        'service_type' => 'Barangay Clearance',
        'description' => 'Request after resident deactivation.',
        'date_requested' => '2026-11-13',
        'status' => 'Pending',
    ]);

    $response->assertSessionHasErrors();

    $this->assertDatabaseMissing('service_requests', [
        'resident_id' => $resident->id,
        'description' => 'Request after resident deactivation.',
    ]);
}
public function test_05_repeated_deactivation_does_not_duplicate_or_modify_resident()
{
    $resident = Resident::factory()->create([
        'status' => 'Active',
    ]);

    $before = (array) DB::table('residents')
        ->where('id', $resident->id)
        ->first();

    $firstResponse = $this->patch(
        route('residents.deactivate', $resident->id)
    );

    $firstResponse->assertRedirect();

    $residentAfterFirst = (array) DB::table('residents')
        ->where('id', $resident->id)
        ->first();

    $this->assertSame('Inactive', $residentAfterFirst['status']);

    $secondResponse = $this->patch(
        route('residents.deactivate', $resident->id)
    );

    $secondResponse->assertRedirect();

    $residentAfterSecond = (array) DB::table('residents')
        ->where('id', $resident->id)
        ->first();

    $this->assertSame($residentAfterFirst, $residentAfterSecond);

    $this->assertSame(
        1,
        DB::table('residents')->where('id', $resident->id)->count()
    );
}
}
