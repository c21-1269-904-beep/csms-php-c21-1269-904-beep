<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'resident_id',
        'service_type',
        'description',
        'date_requested',
        'status',
    ];

    /**
     * Default model attributes.
     */
    protected $attributes = [
        'status' => 'Pending',
    ];

    public function __construct(array $attributes = [])
    {
        // Ensure default status is 'Pending' if not provided
        if (!isset($attributes['status'])) {
            $attributes['status'] = 'Pending';
        }

        parent::__construct($attributes);
    }
}