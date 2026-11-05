<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resident extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'address',
        'contact_number',
        'email',
        'status',
    ];

    // Accessors for camelCase compatibility with T01/T02 tests
    public function getFirstNameAttribute() { return $this->attributes['first_name'] ?? null; }
    public function getLastNameAttribute() { return $this->attributes['last_name'] ?? null; }
    public function getContactNumberAttribute() { return $this->attributes['contact_number'] ?? null; }

    public function setFirstNameAttribute($value) { $this->attributes['first_name'] = $value; }
    public function setLastNameAttribute($value) { $this->attributes['last_name'] = $value; }
    public function setContactNumberAttribute($value) { $this->attributes['contact_number'] = $value; }
}