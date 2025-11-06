<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->create([
            'name'     => 'Owner One',
            'email'    => 'owner1@example.test',
            'password' => Hash::make('password'),
        ]);

        User::factory(6)->create();
    }
}
