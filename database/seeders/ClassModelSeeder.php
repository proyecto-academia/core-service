<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\ClassModel; // O el nombre que hayas usado
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Faker\Factory as Faker;

class ClassModelSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        // Tomamos todos los cursos existentes
        $courses = Course::all();

        if ($courses->isEmpty()) {
            echo "⚠️  No hay cursos para asignar clases.\n";
            return;
        }

        // Autenticación para media
        $authResponse = Http::post(env('AUTH_SERVICE_URL') . '/login', [
            'email' => 'marc@gmail.com',
            'password' => 'password1234',
        ]);

        if (!$authResponse->ok()) {
            echo "❌ Error autenticando: {$authResponse->status()} - {$authResponse->body()}\n";
            return;
        }

        $token = $authResponse->json('data.access_token');

        // Directorio de imágenes de prueba
        $imageDir = storage_path('app/test-assets/classes/photos');
        $imageFiles = File::files($imageDir);

        $videoDir = storage_path('app/test-assets/classes/videos');
        $videoFiles = File::files($videoDir);

        if (empty($imageFiles)) {
            echo "⚠️  No se encontraron imágenes en {$imageDir}\n";
            return;
        }

        foreach ($courses as $course) {
            // Crear entre 2 y 5 clases por curso
            $numClasses = rand(2, 5);
            for ($i = 1; $i <= $numClasses; $i++) {
                $class = $course->classes()->create([
                    'title' => $faker->sentence(4),
                    'description' => $faker->paragraph(),
                    'position' => $i,
                ]);

                $selectedImage = $imageFiles[array_rand($imageFiles)];

                $uploadUrl = rtrim(env('MEDIA_URL', 'https://mardev.es/api/media'), '/') . "/classes/{$class->id}/photo";

                $response = Http::withToken($token)->attach(
                    'file',
                    fopen($selectedImage->getRealPath(), 'r'),
                    $selectedImage->getFilename()
                )->post($uploadUrl);

                if ($response->successful()) {
                    echo "✅ Clase '{$class->title}' creada con imagen en curso {$course->id}\n";
                } else {
                    echo "❌ Falló subida para clase {$class->id}: {$response->status()} - {$response->body()}\n";
                }

                $selectedVideo = $videoFiles[array_rand($videoFiles)];
                $uploadVideoUrl = rtrim(env('MEDIA_URL', 'https://mardev.es/api/media'), '/') . "/classes/{$class->id}/video";
                $videoResponse = Http::withToken($token)->attach(
                    'file',
                    fopen($selectedVideo->getRealPath(), 'r'),
                    $selectedVideo->getFilename()
                )->post($uploadVideoUrl);
                if ($videoResponse->successful()) {
                    echo "✅ Video para clase '{$class->title}' subido correctamente.\n";
                } else {
                    echo "❌ Falló subida de video para clase {$class->id}: {$videoResponse->status()} - {$videoResponse->body()}\n";
                }
            }
        }
    }
}
