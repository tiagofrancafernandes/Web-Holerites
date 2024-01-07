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
                'parent_id' => null,
                'name' => 'Holerites',
                'slug' => 'holerites',
                'description' => 'Holerites',
                'seo_title' => 'Holerites',
                'seo_description' =>  null,
                'show_on_tab_filter' =>  true,
                'order_on_tab_filter' =>  1,
                'icon' =>  null,
                'is_canonical' =>  true,
            ],
            [
                'parent_id' => null,
                'name' => 'Documentos internos',
                'slug' => 'documentos-internos',
                'description' => 'Documentos internos',
                'seo_title' => 'Documentos internos',
                'seo_description' =>  null,
                'show_on_tab_filter' =>  true,
                'order_on_tab_filter' =>  null,
                'icon' =>  null,
                'is_canonical' =>  true,
            ],
            [
                'parent_id' => DocumentCategory::where('slug', 'documentos-internos')->first()?->id,
                'name' => 'Relatórios',
                'slug' => 'relatorios',
                'description' => 'Relatórios',
                'seo_title' => 'Relatórios',
                'seo_description' =>  null,
                'show_on_tab_filter' =>  true,
                'order_on_tab_filter' =>  null,
                'icon' =>  null,
                'is_canonical' =>  true,
            ],
        ];

        foreach ($documentCategories as $documentCategory) {
            DocumentCategory::updateOrCreate([
                'slug' => $documentCategory['slug'],
            ], $documentCategory);
        }
    }
}
