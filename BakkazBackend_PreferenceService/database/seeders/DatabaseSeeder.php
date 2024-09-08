<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Preference;
use App\Models\Privacy;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        Preference::factory()->count(10)->create();
        Privacy::factory()->count(10)->create();
    }
}
