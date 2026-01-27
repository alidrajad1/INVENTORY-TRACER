<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Asset;
use App\Models\Category;
use App\Models\Location;
use App\Models\Employee; // <--- Import Model Employee
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Reset Cache & Setup User/Role (Kode sebelumnya...)
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        
        $roleAdmin = Role::create(['name' => 'super_admin']);
        $roleStaff = Role::create(['name' => 'staff']);

        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole($roleAdmin);

        $staff = User::create([
            'name' => 'Staff',
            'email' => 'staff@example.com',
            'password' => bcrypt('password'),
        ]);
        $staff->assignRole($roleStaff);

        // --- DATA ASET & PEGAWAI ---

        // 2. Buat Kategori (Manual Code)
        $categories = collect([
            ['code' => 'LPT', 'name' => 'Laptop'],
            ['code' => 'PCK', 'name' => 'PC Komputer'],
            ['code' => 'PRJ', 'name' => 'Projector'],
            ['code' => 'FUR', 'name' => 'Furniture'],
            ['code' => 'NET', 'name' => 'Networking'],
        ])->map(function ($cat) {
            return Category::create($cat);
        });

        // 3. Buat Lokasi
        $locations = Location::factory(5)->create();

        // 4. BUAT PEGAWAI (NEW)
        // Kita buat 15 pegawai dummy
        $employees = Employee::factory(15)->create();

        // 5. Buat Asset
        Asset::factory(20)->create(function () use ($categories, $locations) {
            return [
                'category_id' => $categories->random()->id,
                'location_id' => $locations->random()->id,
                'status' => fake()->randomElement(['AVAILABLE', 'BORROWED', 'MAINTENANCE']),
                'condition' => fake()->randomElement(['GOOD', 'BAD', 'BROKEN']),
                
                // Opsional: Jika status BORROWED, kita bisa langsung assign ke random employee di seeder ini
                // Tapi untuk amannya biarkan null dulu (logic assign ada di fitur)
                'employee_id' => null, 
            ];
        });
    }
}