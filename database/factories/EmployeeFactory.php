<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Employee>
 */
class EmployeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            // NIP: Nomor Induk Pegawai (Format: EMP-Angka)
            'nid' => 'EMP-' . $this->faker->unique()->numerify('####'), 
            
            // Jabatan
            'position' => $this->faker->jobTitle(),
            
            // Departemen (Hardcode agar terlihat lebih real untuk kantor/sekolah)
            'department' => $this->faker->randomElement([
                'Information Technology', 
                'Human Resources', 
                'Finance', 
                'General Affair', 
                'Marketing',
                'Operations'
            ]),
            
            // Default pegawai aktif
            'is_active' => true, 
        ];
    }
}