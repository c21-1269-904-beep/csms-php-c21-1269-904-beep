<?php

namespace App\Services;

use App\Models\Resident;
use Illuminate\Support\Facades\Validator;

class ResidentUpdateService
{
    public function update(int $id, array $data): ResidentUpdateResult
    {
        // 1. Retrieve existing resident by ID
        $existing = Resident::find($id);
        if (!$existing) {
            return ResidentUpdateResult::notFound();
        }

        // 2. Validate update data using Laravel Validator
        $validator = Validator::make($data, [
            'first_name'     => 'required|string|max:255',
            'last_name'      => 'required|string|max:255',
            'address'        => 'required|string|max:255',
            'contact_number' => 'required|regex:/^09\d{9}$/',
            'email'          => 'required|email|max:255',
        ]);

        if ($validator->fails()) {
            return ResidentUpdateResult::validationFailed($validator->errors()->toArray());
        }

        // 3. Update permitted fields (preserving ID and status)
        $existing->update([
            'first_name'     => $data['first_name'],
            'last_name'      => $data['last_name'],
            'address'        => $data['address'],
            'contact_number' => (string) $data['contact_number'],
            'email'          => $data['email'],
        ]);

        return ResidentUpdateResult::success($existing);
    }
}