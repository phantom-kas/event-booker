<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Create 2 admins
        User::factory()->count(2)->create([
            'role' => 'admin',
        ]);

        // Create 3 organizers
        User::factory()->count(3)->create([
            'role' => 'organizer',
        ]);

        // Create 10 customers
        User::factory()->count(10)->create([
            'role' => 'customer',
        ]);
    }
}
