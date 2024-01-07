<?php

namespace Database\Factories;

use App\Enums\DocumentStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\StorageFile;
use App\Models\User;
use App\Models\DocumentCategory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Document>
 */
class DocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->words(3, true),
            'slug' => fn(array $attr) => str($attr['name'] ?? fake()->words(3, true))->slug(),
            'release_date' => now(),
            'available_until' => null,
            'status' => DocumentStatus::PUBLISHED,
            'internal_note' => fake()->words(10, true),
            'public_note' => fake()->words(10, true),
            'storage_file_id' => StorageFile::factory(),
            'document_category_id' => DocumentCategory::inRandomOrder()->first() ?: DocumentCategory::factory(),
            'created_by' => User::first() ?: User::factory(),
            'public' => true,
        ];
    }
}
