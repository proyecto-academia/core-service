<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $fillable = [
        'user_id',
        'enrollment_id',
        'amount',
        'payment_method',
        'status',
    ];

    // Relación con Enrollment
    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }
}