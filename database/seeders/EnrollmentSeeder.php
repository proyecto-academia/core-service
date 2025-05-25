<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Enrollment;
use App\Models\Course;
use App\Models\Pack;

class EnrollmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        $userId = 3;// marc@gmail.com 

        if (!$userId) {
            echo "⚠️  No se especificó un ID de usuario.\n";
            return;
        }

        // Inscribir al usuario en cursos aleatorios
        $randomCourses = Course::where('is_free', '!=', true)->inRandomOrder()->take(10)->get(); // Selecciona 10 cursos aleatorios que no sean gratuitos
        foreach ($randomCourses as $course) {
            Enrollment::firstOrCreate([
                'user_id' => $userId,
                'enrollable_type' => Course::class,
                'enrollable_id' => $course->id,
            ], [
                'enrolled_at' => now(),
            ]);
            echo "✅ Usuario {$userId} inscrito en el curso '{$course->name}'.\n";
        }

        // Inscribir al usuario en todos los packs excepto el pack "Premium"
        $packs = Pack::where('name', '!=', 'Premium')->get();
        foreach ($packs as $pack) {
            Enrollment::firstOrCreate([
                'user_id' => $userId,
                'enrollable_type' => Pack::class,
                'enrollable_id' => $pack->id,
            ], [
                'enrolled_at' => now(),
            ]);
            echo "✅ Usuario {$userId} inscrito en el pack '{$pack->name}'.\n";
        }

        echo "🎉 Inscripciones completadas para el usuario {$userId}.\n";
    }
}