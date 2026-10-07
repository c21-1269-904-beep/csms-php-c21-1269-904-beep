<?php

namespace Database\Factories;

use App\Models\Resident;
use Illuminate\Database\Eloquent\Factories\Factory;

class ResidentFactory extends Factory
{
    protected $model = Resident::class;

    public function definition(): array
    {
        return [
            'first_name'     => 'Juan',
            'last_name'      => 'Cruz',
            'address'        => '123 Main Street',
            'contact_number' => '09171234567',
            'email'          => 'juan@example.com',
            'status'         => 'Active',
        ];
    }
}