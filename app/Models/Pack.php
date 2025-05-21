<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pack extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'is_free',
        'published',
    ];

    public function courses()
    {
        return $this->belongsToMany(Course::class);
    }


    public function enrollments()
    {
        return $this->morphMany(Enrollment::class, 'enrollable');
    }


}