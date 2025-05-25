<?php

namespace Database\Seeders;

use App\Models\ClassModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class AddContentToClasses extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        // Structure templates: each entry is a closure returning HTML
        $structures = [
            fn() => '<h2>What will you learn?</h2><ul><li>' . implode('</li><li>', $faker->words(4)) . '</li></ul>',
            fn() => '<p>' . $faker->paragraph . '</p>',
            fn() => '<h3>' . $faker->sentence . '</h3><p>' . $faker->paragraph . '</p>',
            fn() => '<blockquote>' . $faker->sentence . '</blockquote>',
            fn() => '<h2>' . $faker->sentence . '</h2><p>' . $faker->paragraph . '</p><p>' . $faker->paragraph . '</p>',
            fn() => '<ul><li>' . implode('</li><li>', $faker->sentences(3)) . '</li></ul>',
        ];

        // Assuming you already have some classes in DB
        $classes = ClassModel::all(); // adjust to your model name if needed

        foreach ($classes as $class) {
            $content = '';

            // Pick 5 random structures
            foreach (array_rand($structures, 5) as $index) {
                $content .= $structures[$index]() . "\n";
            }

            $class->update(['content' => $content]);
            echo "Updated class ID {$class->id} with content.\n";
            echo "Content: {$content}\n";
        }
    }
}
