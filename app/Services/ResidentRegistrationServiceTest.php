<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\Resident;
use App\Validators\ResidentValidator;
use App\Repositories\InMemoryResidentRepository; // Or your T03 Repository implementation
use App\Services\ResidentRegistrationService;

class ResidentRegistrationServiceTest extends TestCase
{
    private ResidentRegistrationService $service;
    private $repository;

    protected function setUp(): void
    {
        parent::setUp();
        // Uses your existing T03 repository and T02 validator
        $this->repository = new InMemoryResidentRepository();
        $validator = new ResidentValidator();
        $this->service = new ResidentRegistrationService($validator, $this->repository);
    }

    private function makeValidResidentData(array $overrides = []): array
    {
        return array_merge([
            'first_name'     => 'Juan',
            'last_name'      => 'Dela Cruz',
            'address'        => '123 Sampaguita St, San Pedro',
            'contact_number' => '09171234567',
            'email'          => 'juan.delacruz@example.com',
            'status'         => 'Active',
        ], $overrides);
    }

    // Test 1 - Register a Valid Resident
    public function test_register_valid_resident_succeeds(): void
    {
        $resident = new Resident($this->makeValidResidentData());

        $result = $this->service->register($resident);

        $this->assertTrue($result->isSuccess());
        $this->assertEmpty($result->getErrors());
    }

    // Test 2 - Registered Resident Receives an Identifier
    public function test_registered_resident_receives_generated_identifier(): void
    {
        $resident = new Resident($this->makeValidResidentData());
        $this->assertNull($resident->getId()); // Unassigned before registration

        $result = $this->service->register($resident);
        $registered = $result->getResident();

        $this->assertNotNull($registered->getId());
    }

    // Test 3 - Registered Resident Is Persisted
    public function test_registered_resident_is_persisted(): void
    {
        $resident = new Resident($this->makeValidResidentData());
        $result = $this->service->register($resident);

        $generatedId = $result->getResident()->getId();
        $retrieved = $this->repository->findById($generatedId);

        $this->assertNotNull($retrieved);
        $this->assertEquals($generatedId, $retrieved->getId());
    }

    // Test 4 - Registered Resident Information Is Preserved
    public function test_registered_resident_information_is_preserved(): void
    {
        $data = $this->makeValidResidentData([
            'first_name'     => 'Maria',
            'last_name'      => 'Clara',
            'address'        => '456 Real St, Calamba',
            'contact_number' => '09189876543',
            'email'          => 'maria.clara@example.com',
            'status'         => 'Active',
        ]);

        $resident = new Resident($data);
        $result = $this->service->register($resident);

        $retrieved = $this->repository->findById($result->getResident()->getId());

        $this->assertEquals('Maria', $retrieved->getFirstName());
        $this->assertEquals('Clara', $retrieved->getLastName());
        $this->assertEquals('456 Real St, Calamba', $retrieved->getAddress());
        $this->assertEquals('09189876543', $retrieved->getContactNumber()); // Leading zero preserved!
        $this->assertEquals('maria.clara@example.com', $retrieved->getEmail());
        $this->assertEquals('Active', $retrieved->getStatus());
    }

    // Test 5 - Default Active Status Is Preserved
    public function test_default_active_status_is_preserved(): void
    {
        $data = $this->makeValidResidentData();
        unset($data['status']); // Rely on T01 default status initialization

        $resident = new Resident($data);
        $result = $this->service->register($resident);

        $retrieved = $this->repository->findById($result->getResident()->getId());
        $this->assertEquals('Active', $retrieved->getStatus());
    }

    // Test 6 - Invalid Resident Registration Fails
    public function test_invalid_resident_registration_fails(): void
    {
        $data = $this->makeValidResidentData(['first_name' => '']); // Blank first name violates T02
        $resident = new Resident($data);

        $result = $this->service->register($resident);

        $this->assertFalse($result->isSuccess());
    }

    // Test 7 - Invalid Resident Is Not Persisted
    public function test_invalid_resident_is_not_persisted(): void
    {
        $initialCount = count($this->repository->findAll());

        $data = $this->makeValidResidentData(['contact_number' => '12345']); // Invalid phone format
        $resident = new Resident($data);

        $this->service->register($resident);

        // Verify repository record count did not change
        $this->assertCount($initialCount, $this->repository->findAll());
    }

    // Test 8 - Validation Failure Can Be Identified
    public function test_validation_failure_can_be_identified(): void
    {
        $data = $this->makeValidResidentData(['first_name' => '   ']); // Whitespace-only
        $resident = new Resident($data);

        $result = $this->service->register($resident);

        $this->assertFalse($result->isSuccess());
        $this->assertTrue($result->hasErrorForField('first_name'));
    }
}