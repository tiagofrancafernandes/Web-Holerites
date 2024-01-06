<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DocumentCategory>
 */
class DocumentCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => str(fake()->words(3, true))->title(),
            'slug' => fn(array $attr) => str($attr['name'] ?? fake()->words(3, true))->slug(),
            'icon' => null,
            'show_on_tab_filter' => fake()->boolean(90),
            'order_on_tab_filter' => rand(1, 10),
        ];
    }
}
