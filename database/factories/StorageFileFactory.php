<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Filament\Resources\DocumentResource;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StorageFile>
 */
class StorageFileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'disk_name' => DocumentResource::getDocumentDisk(),
            'path' => 'example.pdf',
            'extension' => 'pdf',
            'size_in_kb' => 17432,
            'file_name' => 'example.pdf',
            'original_name' => 'example.pdf',
            'public' => true,
            'uploaded_by' => null,
            'reference_class' => null,
        ];
    }
}
