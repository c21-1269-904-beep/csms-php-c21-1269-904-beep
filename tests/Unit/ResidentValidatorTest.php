<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Validators\ResidentValidator;

class ResidentValidatorTest extends TestCase
{
    private ResidentValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new ResidentValidator();
    }

    private function validResidentData(): array
    {
        return [
            'first_name' => 'Juan',
            'last_name' => 'Cruz',
            'address' => '123 Main Street',
            'contact_number' => '09171234567',
            'email' => 'juan@example.com',
        ];
    }

    public function test_valid_resident_information_passes_validation(): void
    {
        $result = $this->validator->validate($this->validResidentData());

        $this->assertTrue($result->isValid());
        $this->assertEmpty($result->getErrors());
    }

    public function test_missing_first_name_fails_validation(): void
    {
        $data = $this->validResidentData();
        $data['first_name'] = '';

        $result = $this->validator->validate($data);

        $this->assertFalse($result->isValid());
        $this->assertArrayHasKey('first_name', $result->getErrors());
    }

    public function test_missing_last_name_fails_validation(): void
    {
        $data = $this->validResidentData();
        $data['last_name'] = '';

        $result = $this->validator->validate($data);

        $this->assertFalse($result->isValid());
        $this->assertArrayHasKey('last_name', $result->getErrors());
    }

    public function test_missing_address_fails_validation(): void
    {
        $data = $this->validResidentData();
        $data['address'] = '';

        $result = $this->validator->validate($data);

        $this->assertFalse($result->isValid());
        $this->assertArrayHasKey('address', $result->getErrors());
    }

    public function test_invalid_contact_number_fails_validation(): void
    {
        $data = $this->validResidentData();
        $data['contact_number'] = '12345';

        $result = $this->validator->validate($data);

        $this->assertFalse($result->isValid());
        $this->assertArrayHasKey('contact_number', $result->getErrors());
    }

    public function test_invalid_email_fails_validation(): void
    {
        $data = $this->validResidentData();
        $data['email'] = 'invalid-email';

        $result = $this->validator->validate($data);

        $this->assertFalse($result->isValid());
        $this->assertArrayHasKey('email', $result->getErrors());
    }

    public function test_multiple_invalid_fields_return_errors(): void
    {
        $data = [
            'first_name' => '',
            'last_name' => '',
            'address' => '',
            'contact_number' => '123',
            'email' => 'invalid',
        ];

        $result = $this->validator->validate($data);

        $this->assertFalse($result->isValid());
        $this->assertCount(5, $result->getErrors());
    }
}
