<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Project;
use Illuminate\Database\Seeder;

class MarketplaceSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['name' => 'Handmade', 'slug' => 'handmade'],
            ['name' => 'Food', 'slug' => 'food'],
            ['name' => 'Stationery', 'slug' => 'stationery'],
            ['name' => 'Tech', 'slug' => 'tech'],
        ] as $category) {
            Category::updateOrCreate(['slug' => $category['slug']], $category);
        }

        $projects = [
            ['Green Economy', 1], ['Robotics Lab', 2], ['Urban Farming', 3], ['Digital Art', 4],
            ['Water Cycle', 5], ['Local History', 6], ['Ocean Cleanup', 1], ['Paper Craft', 2],
            ['Coding Club', 3], ['Textile Design', 4], ['Solar Cars', 5], ['Community Garden', 6],
        ];

        foreach ($projects as $index => [$name, $grade]) {
            Project::updateOrCreate(
                ['class_name' => 'Class '.($index + 1)],
                [
                    'grade' => $grade,
                    'title' => $name,
                    'introduction' => 'Class '.($index + 1)." explored {$name} through a hands-on term-long project.",
                    'story' => 'Students researched, prototyped and improved their ideas together, sharing progress with their school community along the way.',
                    'outcome' => 'The class turned what they learned into a project and products they can share with the community.',
                    'is_published' => true,
                ],
            );
        }
    }
}
