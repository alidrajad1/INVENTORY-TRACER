<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
       $name = ucfirst($this->faker->unique()->word);
       $code = strtoupper(substr($name, 0, 3)) . $this->faker->numerify('##');
        return [
            'name',
            'code',
            // Jika tabel Anda punya kolom slug atau description, tambahkan di sini:
            // 'slug' => \Illuminate\Support\Str::slug($name),
            // 'description' => $this->faker->sentence,
        ];
    }
}