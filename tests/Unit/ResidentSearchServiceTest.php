<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Resident;
use App\Repositories\InMemoryResidentRepository;
use App\Services\ResidentSearchService;

class ResidentSearchServiceTest extends TestCase
{
    private InMemoryResidentRepository $repository;
    private ResidentSearchService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new InMemoryResidentRepository();
        $this->service = new ResidentSearchService($this->repository);
    }

    private function createResident(array $overrides): Resident
    {
        $data = array_merge([
            'first_name'     => 'First',
            'last_name'      => 'Last',
            'address'        => '123 Street',
            'contact_number' => '09171234567',
            'email'          => 'test@example.com',
            'status'         => 'Active',
        ], $overrides);

        return $this->repository->save(new Resident($data));
    }

    // Test 1 - List All Persisted Residents
    public function test_list_all_persisted_residents(): void
    {
        $this->createResident(['first_name' => 'Juan', 'last_name' => 'Cruz']);
        $this->createResident(['first_name' => 'Maria', 'last_name' => 'Santos']);

        $results = $this->service->listResidents();
        $this->assertCount(2, $results);
    }

    // Test 2 - Empty Resident Listing
    public function test_empty_resident_listing_returns_empty_array(): void
    {
        $results = $this->service->listResidents();
        $this->assertIsArray($results);
        $this->assertEmpty($results);
    }

    // Test 3 - Resident Listing Uses Required Ordering
    public function test_resident_listing_uses_required_ordering(): void
    {
        $this->createResident(['first_name' => 'Ana', 'last_name' => 'Santos']);
        $this->createResident(['first_name' => 'Pedro', 'last_name' => 'Cruz']);
        $this->createResident(['first_name' => 'Maria', 'last_name' => 'Andres']);
        $this->createResident(['first_name' => 'Juan', 'last_name' => 'Cruz']);

        $results = $this->service->listResidents();

        $this->assertEquals('Andres', $results[0]->getLastName());
        $this->assertEquals('Cruz', $results[1]->getLastName());
        $this->assertEquals('Juan', $results[1]->getFirstName());
        $this->assertEquals('Cruz', $results[2]->getLastName());
        $this->assertEquals('Pedro', $results[2]->getFirstName());
        $this->assertEquals('Santos', $results[3]->getLastName());
    }

    // Test 4 - Partial First Name Search Is Case-Insensitive
    public function test_partial_first_name_search_is_case_insensitive(): void
    {
        $this->createResident(['first_name' => 'Juan', 'last_name' => 'Dela Cruz']);

        $results = $this->service->searchResidents('jUa');
        $this->assertCount(1, $results);
        $this->assertEquals('Juan', $results[0]->getFirstName());
    }

    // Test 5 - Partial Last Name Search Is Case-Insensitive
    public function test_partial_last_name_search_is_case_insensitive(): void
    {
        $this->createResident(['first_name' => 'Juan', 'last_name' => 'Dela Cruz']);

        $results = $this->service->searchResidents('cRuZ');
        $this->assertCount(1, $results);
        $this->assertEquals('Dela Cruz', $results[0]->getLastName());
    }

    // Test 6 - Blank Search Returns All Residents
    public function test_blank_search_returns_all_residents(): void
    {
        $this->createResident(['first_name' => 'Juan', 'last_name' => 'Cruz']);
        $this->createResident(['first_name' => 'Maria', 'last_name' => 'Santos']);

        $results = $this->service->searchResidents('   ');
        $this->assertCount(2, $results);
    }

    // Test 7 - Search With No Match Returns Empty Collection
    public function test_search_with_no_match_returns_empty_collection(): void
    {
        $this->createResident(['first_name' => 'Juan', 'last_name' => 'Cruz']);

        $results = $this->service->searchResidents('ZzzUnknown');
        $this->assertIsArray($results);
        $this->assertEmpty($results);
    }

    // Test 8 - Search Results Preserve Resident Information
    public function test_search_results_preserve_resident_information(): void
    {
        $this->createResident([
            'first_name'     => 'Maria',
            'last_name'      => 'Clara',
            'address'        => '456 Real St',
            'contact_number' => '09171234567',
            'email'          => 'maria@example.com',
            'status'         => 'Active'
        ]);

        $results = $this->service->searchResidents('Maria');
        $resident = $results[0];

        $this->assertNotNull($resident->getId());
        $this->assertEquals('Maria', $resident->getFirstName());
        $this->assertEquals('Clara', $resident->getLastName());
        $this->assertEquals('456 Real St', $resident->getAddress());
        $this->assertEquals('09171234567', $resident->getContactNumber());
        $this->assertEquals('maria@example.com', $resident->getEmail());
        $this->assertEquals('Active', $resident->getStatus());
    }

    // Test 9 - Active and Inactive Residents Are Included
    public function test_active_and_inactive_residents_are_included(): void
    {
        $this->createResident(['first_name' => 'Juan', 'last_name' => 'Cruz', 'status' => 'Active']);
        $this->createResident(['first_name' => 'Pedro', 'last_name' => 'Cruz', 'status' => 'Inactive']);

        $results = $this->service->searchResidents('Cruz');
        $this->assertCount(2, $results);
    }

    // Test 10 - Matching Resident Is Not Duplicated
    public function test_matching_resident_is_not_duplicated(): void
    {
        $this->createResident(['first_name' => 'Chris', 'last_name' => 'Christian']);

        $results = $this->service->searchResidents('Chris');
        $this->assertCount(1, $results);
    }
}