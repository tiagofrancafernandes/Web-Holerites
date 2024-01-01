<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\DocumentCategory;

class DocumentCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $documentCategories = [
            [
                'name' => 'Holerites',
                'slug' => 'holerites',
                'description' => 'Holerites',
                'seo_title' => 'Holerites',
            ],
        ];

        foreach($documentCategories as $documentCategory) {
            DocumentCategory::updateOrCreate([
                'slug' => $documentCategory['slug'],
            ], $documentCategory);
        }
    }
}
