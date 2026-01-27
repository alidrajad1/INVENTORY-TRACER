<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Category;
use App\Models\Location;    

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Asset>
 */
class AssetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
            'asset_tag' => 'AST-' . $this->faker->unique()->numberBetween(1000, 9999),
            'status' => 'AVAILABLE',
            'serial_number' => $this->faker->unique()->isbn13(), 
            'category_id' => Category::factory(), 
            'location_id' => Location::factory(),
        ];
    }
}
