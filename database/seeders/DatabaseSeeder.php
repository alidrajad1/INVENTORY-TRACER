<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Asset;
use App\Models\Category;
use App\Models\Location;
use App\Models\Employee;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {

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

        $categories = collect([
            ['code' => 'LPT', 'name' => 'Laptop'],
            ['code' => 'PCK', 'name' => 'PC Komputer'],
            ['code' => 'PRJ', 'name' => 'Projector'],
            ['code' => 'FUR', 'name' => 'Furniture'],
            ['code' => 'NET', 'name' => 'Networking'],
        ])->map(function ($cat) {
            return Category::create($cat);
        });

        $locations = Location::factory(5)->create();

        $employees = Employee::factory(15)->create();

        Asset::factory(20)->create(function () use ($categories, $locations) {
            return [
                'category_id' => $categories->random()->id,
                'location_id' => $locations->random()->id,
                'status' => fake()->randomElement(['AVAILABLE', 'BORROWED', 'MAINTENANCE']),
                'condition' => fake()->randomElement(['GOOD', 'BAD', 'BROKEN']),
                'employee_id' => null, 
            ];
        });
    }
}