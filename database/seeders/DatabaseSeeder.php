<?php

namespace Database\Seeders;

use App\Models\Component;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Main account
        User::factory()->create([
            'name' => 'Admin',
            'username' => 'admin',
            'email' => 'admin@admin.com',
            'is_admin' => true
        ]);

        User::factory()
            ->count(15)
            ->create()->each(function(User $user){

                // Components
                Component::factory()
                         ->count(rand(0, 5))
                         ->create(['author_id' => $user->id]);
            });
    }
}
