<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Location>
 */
class LocationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Kita buat variasi nama lokasi agar terlihat nyata
        $prefixes = ['Ruang', 'Gedung', 'Lab', 'Gudang', 'Kantor'];
        $suffix   = $this->faker->unique()->bothify('??-##'); // Contoh: AB-01, XZ-99
        
        // Atau cara lebih simpel: "Ruang 101", "Ruang 102"
        return [
            'name' => $this->faker->randomElement($prefixes) . ' ' . $this->faker->unique()->numberBetween(100, 500),
            
            // Opsional: Jika ada kolom alamat/deskripsi
            // 'description' => $this->faker->address,
        ];
    }
}