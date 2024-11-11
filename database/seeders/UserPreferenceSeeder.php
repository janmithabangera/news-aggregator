<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserPreferenceSeeder extends Seeder
{
    public function run()
    {
        // create one
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'),
                'remember_token' => Str::random(60),
            ]
        );

        // Create preferences for admin
        UserPreference::create([
            'user_id' => $admin->id,
            'preferred_sources' => ['NewsAPI', 'Guardian', 'NYT'],
            'preferred_categories' => ['technology', 'business'],
            'preferred_authors' => [],
        ]);

        $this->command->info('User preferences created successfully!');
    }
}
