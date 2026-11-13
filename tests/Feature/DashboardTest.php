<?php

namespace Tests\Feature;

use App\Models\Resident;
use App\Repositories\ResidentRepository;
use App\Repositories\ServiceRequestRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private function createResident(string $status = 'Active'): Resident
    {
        return Resident::factory()->create([
            'status' => $status,
        ]);
    }

    private function createRequest(
        Resident $resident,
        string $status = 'Pending',
        string $date = '2026-11-13',
        string $serviceType = 'Barangay Clearance'
    ) {
        return (new ServiceRequestRepository())->save([
            'resident_id' => $resident->id,
            'service_type' => $serviceType,
            'description' => 'Dashboard automated test request.',
            'date_requested' => $date,
            'status' => $status,
        ]);
    }

    public function test_01_dashboard_page_loads(): void
    {
        $this->get('/dashboard')
            ->assertOk()
            ->assertViewIs('dashboard.index');
    }

    public function test_02_empty_dashboard_displays_zero_counts(): void
    {
        $this->get('/dashboard')
            ->assertOk()
            ->assertViewHas('residentTotal', 0)
            ->assertViewHas('activeResidents', 0)
            ->assertViewHas('inactiveResidents', 0)
            ->assertViewHas('serviceRequestTotal', 0)
            ->assertViewHas('pendingRequests', 0)
            ->assertViewHas('inProgressRequests', 0)
            ->assertViewHas('completedRequests', 0)
            ->assertViewHas('cancelledRequests', 0)
            ->assertViewHas('openRequests', 0)
            ->assertViewHas('recentServiceRequests', []);
    }

    public function test_03_resident_counts_are_correct(): void
    {
        $this->createResident('Active');
        $this->createResident('Active');
        $this->createResident('Inactive');

        $this->get('/dashboard')
            ->assertViewHas('residentTotal', 3)
            ->assertViewHas('activeResidents', 2)
            ->assertViewHas('inactiveResidents', 1);
    }

    public function test_04_resident_total_equals_active_plus_inactive(): void
    {
        $this->createResident('Active');
        $this->createResident('Inactive');
        $this->createResident('Active');

        $response = $this->get('/dashboard');

        $response->assertViewHas('residentTotal', function ($total) use ($response) {
            return $total ===
                $response->viewData('activeResidents') +
                $response->viewData('inactiveResidents');
        });
    }

    public function test_05_service_request_total_is_correct(): void
    {
        $resident = $this->createResident();

        $this->createRequest($resident, 'Pending');
        $this->createRequest($resident, 'Completed');
        $this->createRequest($resident, 'Cancelled');

        $this->get('/dashboard')
            ->assertViewHas('serviceRequestTotal', 3);
    }

    public function test_06_service_request_status_counts_are_correct(): void
    {
        $resident = $this->createResident();

        $this->createRequest($resident, 'Pending');
        $this->createRequest($resident, 'Pending');
        $this->createRequest($resident, 'In Progress');
        $this->createRequest($resident, 'Completed');
        $this->createRequest($resident, 'Cancelled');

        $this->get('/dashboard')
            ->assertViewHas('pendingRequests', 2)
            ->assertViewHas('inProgressRequests', 1)
            ->assertViewHas('completedRequests', 1)
            ->assertViewHas('cancelledRequests', 1);
    }

    public function test_07_service_request_status_counts_reconcile_with_total(): void
    {
        $resident = $this->createResident();

        $this->createRequest($resident, 'Pending');
        $this->createRequest($resident, 'In Progress');
        $this->createRequest($resident, 'Completed');
        $this->createRequest($resident, 'Cancelled');

        $response = $this->get('/dashboard');

        $statusTotal =
            $response->viewData('pendingRequests') +
            $response->viewData('inProgressRequests') +
            $response->viewData('completedRequests') +
            $response->viewData('cancelledRequests');

        $this->assertSame(
            $response->viewData('serviceRequestTotal'),
            $statusTotal
        );
    }

    public function test_08_open_requests_equal_pending_plus_in_progress(): void
    {
        $resident = $this->createResident();

        $this->createRequest($resident, 'Pending');
        $this->createRequest($resident, 'Pending');
        $this->createRequest($resident, 'In Progress');
        $this->createRequest($resident, 'Completed');

        $this->get('/dashboard')
            ->assertViewHas('openRequests', 3);
    }

    public function test_09_dashboard_reflects_resident_deactivation(): void
    {
        $resident = $this->createResident('Active');

        (new ResidentRepository())->deactivateById($resident->id);

        $this->get('/dashboard')
            ->assertViewHas('residentTotal', 1)
            ->assertViewHas('activeResidents', 0)
            ->assertViewHas('inactiveResidents', 1);
    }

    public function test_10_dashboard_reflects_service_request_status_changes(): void
    {
        $resident = $this->createResident();

        $request = $this->createRequest($resident, 'Pending');

        (new ServiceRequestRepository())->update($request->id, [
            'status' => 'In Progress',
        ]);

        $this->get('/dashboard')
            ->assertViewHas('pendingRequests', 0)
            ->assertViewHas('inProgressRequests', 1)
            ->assertViewHas('openRequests', 1);
    }

    public function test_11_completed_request_is_removed_from_open_count(): void
    {
        $resident = $this->createResident();

        $request = $this->createRequest($resident, 'In Progress');

        (new ServiceRequestRepository())->update($request->id, [
            'status' => 'Completed',
        ]);

        $this->get('/dashboard')
            ->assertViewHas('inProgressRequests', 0)
            ->assertViewHas('completedRequests', 1)
            ->assertViewHas('openRequests', 0);
    }

    public function test_12_cancelled_request_is_removed_from_open_count(): void
    {
        $resident = $this->createResident();

        $request = $this->createRequest($resident, 'Pending');

        (new ServiceRequestRepository())->update($request->id, [
            'status' => 'Cancelled',
        ]);

        $this->get('/dashboard')
            ->assertViewHas('pendingRequests', 0)
            ->assertViewHas('cancelledRequests', 1)
            ->assertViewHas('openRequests', 0);
    }

    public function test_13_recent_requests_are_limited_to_five_and_ordered_correctly(): void
    {
        $resident = $this->createResident();

        for ($i = 1; $i <= 7; $i++) {
            $this->createRequest(
                $resident,
                'Pending',
                '2026-11-' . str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                'Test Request ' . $i
            );
        }

        $response = $this->get('/dashboard');

        $recent = $response->viewData('recentServiceRequests');

        $this->assertCount(5, $recent);
        $this->assertSame('Test Request 7', $recent[0]->service_type);
        $this->assertSame('Test Request 3', $recent[4]->service_type);
    }

    public function test_14_completed_and_cancelled_requests_can_appear_in_recent_list(): void
    {
        $resident = $this->createResident();

        $this->createRequest($resident, 'Completed');
        $this->createRequest($resident, 'Cancelled');

        $response = $this->get('/dashboard');

        $statuses = collect(
            $response->viewData('recentServiceRequests')
        )->pluck('status')->all();

        $this->assertContains('Completed', $statuses);
        $this->assertContains('Cancelled', $statuses);
    }

    public function test_15_loading_dashboard_does_not_modify_database_records(): void
    {
        $resident = $this->createResident();

        $this->createRequest($resident, 'Pending');

        $residentCountBefore = DB::table('residents')->count();
        $requestCountBefore = DB::table('service_requests')->count();

        $this->get('/dashboard')->assertOk();
        $this->get('/dashboard')->assertOk();

        $this->assertSame(
            $residentCountBefore,
            DB::table('residents')->count()
        );

        $this->assertSame(
            $requestCountBefore,
            DB::table('service_requests')->count()
        );
    }
}
