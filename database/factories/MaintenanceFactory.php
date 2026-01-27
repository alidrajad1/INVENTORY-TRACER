<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Asset;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Maintenance>
 */
class MaintenanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),   // Maintenance butuh User (Pelapor/Admin)
            'asset_id' => Asset::factory(), // Maintenance butuh Aset
            'description' => $this->faker->sentence(),

            'status' => 'scheduled',
            'scheduled_at' => now()->addDays(rand(1, 7)),
            'completed_at' => null,
        ];
    }
}
