<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Resident;
use App\Models\ServiceRequest;
use App\Repositories\ServiceRequestRepository;
use App\Services\ServiceRequestQueryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class ServiceRequestQueryTest extends TestCase
{
    use RefreshDatabase;

    protected ServiceRequestRepository $repository;
    protected ServiceRequestQueryService $queryService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new ServiceRequestRepository();
        $this->queryService = new ServiceRequestQueryService(
            $this->repository
        );
    }

    private function createRequest(array $overrides = []): ServiceRequest
    {
        $resident = Resident::factory()->create([
            'status' => 'Active',
        ]);

        return $this->repository->save(array_merge([
            'resident_id' => $resident->id,
            'service_type' => 'Barangay Clearance',
            'description' => 'Request for employment purposes.',
            'date_requested' => '2026-10-07',
            'status' => 'Pending',
        ], $overrides));
    }

    // Test 1: List all persisted requests.
    public function test_01_lists_all_persisted_service_requests()
    {
        $this->createRequest();
        $this->createRequest([
            'service_type' => 'Business Permit',
        ]);

        $result = $this->queryService->listAll();

        $this->assertCount(2, $result);
        $this->assertContainsOnlyInstancesOf(ServiceRequest::class, $result);
    }

    // Test 2: Empty listing returns an empty collection.
    public function test_02_empty_listing_returns_empty_array()
    {
        $result = $this->queryService->listAll();

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    // Test 3: Order by date descending, then ID descending.
    public function test_03_lists_requests_in_required_deterministic_order()
    {
        $oldest = $this->createRequest([
            'date_requested' => '2026-10-07',
        ]);

        $sameDateFirst = $this->createRequest([
            'date_requested' => '2026-10-10',
        ]);

        $newest = $this->createRequest([
            'date_requested' => '2026-10-10',
        ]);

        $result = $this->queryService->listAll();

        $this->assertSame(
            [$newest->id, $sameDateFirst->id, $oldest->id],
            array_map(fn ($request) => $request->id, $result)
        );
    }

    // Test 4: View an existing request by ID.
    public function test_04_views_existing_service_request_by_id()
    {
        $request = $this->createRequest();

        $result = $this->queryService->viewById($request->id);

        $this->assertNotNull($result);
        $this->assertEquals($request->id, $result->id);
    }

    // Test 5: Unknown ID safely returns null without creating records.
    public function test_05_unknown_id_returns_null_without_creating_record()
    {
        $before = DB::table('service_requests')->count();

        $result = $this->queryService->viewById(99999);

        $after = DB::table('service_requests')->count();

        $this->assertNull($result);
        $this->assertEquals($before, $after);
    }

    // Test 6: Partial, case-insensitive service type search.
    public function test_06_searches_service_type_partially_and_case_insensitively()
    {
        $this->createRequest([
            'service_type' => 'Barangay Clearance',
        ]);

        $this->createRequest([
            'service_type' => 'Business Permit',
        ]);

        $result = $this->queryService->search('CLEAR');

        $this->assertTrue($result->isSuccess());
        $this->assertCount(1, $result->getServiceRequests());
        $this->assertEquals(
            'Barangay Clearance',
            $result->getServiceRequests()[0]->service_type
        );
    }

    // Test 7: Partial, case-insensitive description search.
    public function test_07_searches_description_partially_and_case_insensitively()
    {
        $this->createRequest([
            'description' => 'Needed for EMPLOYMENT purposes.',
        ]);

        $this->createRequest([
            'description' => 'Needed for business registration.',
        ]);

        $result = $this->queryService->search('employment');

        $this->assertTrue($result->isSuccess());
        $this->assertCount(1, $result->getServiceRequests());
        $this->assertEquals(
            'Needed for EMPLOYMENT purposes.',
            $result->getServiceRequests()[0]->description
        );
    }

    // Test 8: Blank search returns all requests in normal order.
    public function test_08_blank_search_returns_all_requests_in_required_order()
    {
        $this->createRequest([
            'date_requested' => '2026-10-07',
        ]);

        $this->createRequest([
            'date_requested' => '2026-10-10',
        ]);

        $allRequests = $this->queryService->listAll();
        $result = $this->queryService->search('   ');

        $this->assertTrue($result->isSuccess());
        $this->assertEquals(
            array_map(fn ($r) => $r->id, $allRequests),
            array_map(fn ($r) => $r->id, $result->getServiceRequests())
        );
    }

    // Test 9: No matching search returns an empty array.
    public function test_09_no_matching_search_returns_empty_array()
    {
        $this->createRequest();

        $result = $this->queryService->search('nonexistent keyword');

        $this->assertTrue($result->isSuccess());
        $this->assertEmpty($result->getServiceRequests());
    }

    // Test 10: Status filter returns only matching requests.
    public function test_10_status_filter_returns_only_matching_requests()
    {
        $this->createRequest(['status' => 'Pending']);
        $this->createRequest(['status' => 'Completed']);
        $this->createRequest(['status' => 'Cancelled']);

        $result = $this->queryService->search(null, 'Completed');

        $this->assertTrue($result->isSuccess());
        $this->assertCount(1, $result->getServiceRequests());
        $this->assertEquals(
            'Completed',
            $result->getServiceRequests()[0]->status
        );
    }

    // Test 11: No status filter includes all lifecycle states.
    public function test_11_no_status_filter_includes_all_lifecycle_states()
    {
        $statuses = [
            'Pending',
            'In Progress',
            'Completed',
            'Cancelled',
        ];

        foreach ($statuses as $status) {
            $this->createRequest(['status' => $status]);
        }

        $result = $this->queryService->search();

        $this->assertTrue($result->isSuccess());

        $actualStatuses = array_unique(
            array_map(
                fn ($request) => $request->status,
                $result->getServiceRequests()
            )
        );

        $this->assertEqualsCanonicalizing($statuses, $actualStatuses);
    }

    // Test 12: Combined search requires both text and status conditions.
    public function test_12_combined_text_and_status_filters_require_both_conditions()
    {
        $this->createRequest([
            'service_type' => 'Barangay Clearance',
            'status' => 'Pending',
        ]);

        $this->createRequest([
            'service_type' => 'Barangay Clearance',
            'status' => 'Completed',
        ]);

        $this->createRequest([
            'service_type' => 'Business Permit',
            'status' => 'Completed',
        ]);

        $result = $this->queryService->search(
            'Clearance',
            'Completed'
        );

        $this->assertTrue($result->isSuccess());
        $this->assertCount(1, $result->getServiceRequests());
        $this->assertEquals(
            'Barangay Clearance',
            $result->getServiceRequests()[0]->service_type
        );
        $this->assertEquals(
            'Completed',
            $result->getServiceRequests()[0]->status
        );
    }

    // Test 13: Matching both service type and description returns once.
    public function test_13_request_matching_both_search_fields_appears_only_once()
    {
        $this->createRequest([
            'service_type' => 'Employment Clearance',
            'description' => 'Employment requirement.',
        ]);

        $result = $this->queryService->search('employment');

        $this->assertTrue($result->isSuccess());
        $this->assertCount(1, $result->getServiceRequests());
    }

    // Test 14: Query results preserve all Service Request information.
    public function test_14_query_results_preserve_all_service_request_fields()
    {
        $request = $this->createRequest([
            'service_type' => 'Indigency Certificate',
            'description' => 'Financial assistance requirement.',
            'date_requested' => '2026-10-12',
            'status' => 'Completed',
        ]);

        $result = $this->queryService->search('Indigency');

        $this->assertTrue($result->isSuccess());
        $this->assertCount(1, $result->getServiceRequests());

        $retrieved = $result->getServiceRequests()[0];

        $this->assertEquals($request->id, $retrieved->id);
        $this->assertEquals($request->resident_id, $retrieved->resident_id);
        $this->assertEquals('Indigency Certificate', $retrieved->service_type);
        $this->assertEquals(
            'Financial assistance requirement.',
            $retrieved->description
        );
        $this->assertEquals('2026-10-12', $retrieved->date_requested);
        $this->assertEquals('Completed', $retrieved->status);
    }

    // Additional Test 15: Unsupported status filter is rejected.
    public function test_15_unsupported_status_filter_is_rejected()
    {
        $this->createRequest();

        $result = $this->queryService->search(null, 'Archived');

        $this->assertTrue($result->isUnsupportedStatus());
        $this->assertEmpty($result->getServiceRequests());
        $this->assertArrayHasKey('status', $result->getErrors());
    }
}
