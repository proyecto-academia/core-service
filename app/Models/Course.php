<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'is_free',
        'estimated_hours',
        'price',
        'published',
    ];

    // Relación: un curso tiene muchas clases (lecciones)
    public function classes()
    {
        return $this->hasMany(ClassModel::class); // Asumiendo que usaremos ClassLesson como modelo
    }

    // Relación: un curso puede pertenecer a muchos packs
    public function packs()
    {
        return $this->belongsToMany(Pack::class);
    }

    // Relación: muchos usuarios pueden estar inscritos en un curso
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'enrollable_id')
            ->where('enrollable_type', Course::class);
    }



}