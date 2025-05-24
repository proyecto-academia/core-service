<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use App\Models\Course;
use Faker\Factory as Faker;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        // 1. Carpeta con imágenes
        $imageDir = storage_path('app/test-assets/courses/photos');
        $imageFiles = File::files($imageDir);

        if (empty($imageFiles)) {
            echo "⚠️  No se encontraron imágenes en {$imageDir}\n";
            return;
        }

        // 2. Autenticación al microservicio auth
        $authResponse = Http::post(env('AUTH_SERVICE_URL') . '/login', [
            'email' => 'marc@gmail.com',
            'password' => 'password1234',
        ]);

        if (!$authResponse->successful()) {
            echo "❌ Falló login: {$authResponse->status()} - {$authResponse->body()}\n";
            return;
        }

        $token = $authResponse->json('data')['access_token'] ?? null;

        if (!$token) {
            echo "❌ No se recibió token de autenticación.\n";
            return;
        }

        $mediaUrl = rtrim(env('MEDIA_URL', 'https://mardev.es/api/media'), '/');

        // 3. Crear 51 cursos
        for ($i = 1; $i <= 51; $i++) {

            $is_free = $faker->boolean(30);
            $price = $is_free ? 0.00 : $faker->randomFloat(2, 9.99, 199.99);
            $course = Course::create([
                'name' => $faker->catchPhrase,
                'description' => $faker->paragraph(3),
                'estimated_hours' => $faker->numberBetween(5, 40),
                'is_free' => $is_free,
                'price' => $price,
                'published' => $faker->boolean(70),
            ]);

            $selectedImage = $imageFiles[array_rand($imageFiles)];
            $uploadUrl = "{$mediaUrl}/courses/{$course->id}/photo";

            $response = Http::withToken($token)->attach(
                'file',
                fopen($selectedImage->getRealPath(), 'r'),
                $selectedImage->getFilename()
            )->post($uploadUrl);

            if ($response->successful()) {
                $path = $response->json('path');
                echo "✅ [$i/51] Curso '{$course->name}' creado con imagen: {$path}\n";
            } else {
                echo "❌ [$i/51] Falló subida para curso {$course->id}: {$response->status()} - {$response->body()}\n";
            }
        }
    }
}
