<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Pack;
use App\Models\Course;

class CreatePacks extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $freePack = Pack::create([
            'name' => 'Free',
            'description' => 'Contains all free courses',
            'price' => 0.00,
            'is_free' => true,
            'published' => true,
        ]);

        $advancedPack = Pack::create([
            'name' => 'Advanced',
            'description' => 'Contains all courses with price > 0 and price < 100',
            'price' => 50.00,
            'is_free' => false,
            'published' => true,
        ]);

        $premiumPack = Pack::create([
            'name' => 'Premium',
            'description' => 'Contains all courses with price >= 100',
            'price' => 150.00,
            'is_free' => false,
            'published' => true,
        ]);

        // Attach courses to the advanced pack

        $freeCourses = Course::where('is_free', true)->get();
        $freePack->courses()->attach($freeCourses->pluck('id'));
        echo "Free Pack created with " . $freeCourses->count() . " free courses.\n";

        $advancedCourses = Course::where('price', '>', 0)
            ->where('price', '<', 100)
            ->get();

        $advancedPack->courses()->attach($advancedCourses->pluck('id'));

        echo "Advanced Pack created with " . $advancedCourses->count() . " advanced courses.\n";
        // Attach courses to the premium pack
        $premiumCourses = Course::where('price', '>=', 100)->get();

        $premiumPack->courses()->attach($premiumCourses->pluck('id'));
        echo "Premium Pack created with " . $premiumCourses->count() . " premium courses.\n";
    }
}
