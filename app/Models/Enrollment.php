<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Enrollment extends Model
{
    protected $fillable = [
        'user_id',
        'enrollable_id',
        'enrollable_type',
        'enrolled_at',
    ];

    public function enrollable()
    {
        return $this->morphTo();
    }

    public function purchase()
    {
        return $this->hasOne(Purchase::class);
    }

}
