<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'resident_id',
        'service_type',
        'description',
        'date_requested',
        'status',
    ];

    protected $attributes = [
        'status' => 'Pending',
    ];

    public function resident()
    {
        return $this->belongsTo(Resident::class);
    }
}