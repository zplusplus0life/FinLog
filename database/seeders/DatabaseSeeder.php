<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Explicitly create Admin user
        User::create([
            'nama_lengkap' => 'Admin User',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => 1,
        ]);

        // Explicitly create Manajer user
        User::create([
            'nama_lengkap' => 'Manajer User',
            'email' => 'manajer@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'manajer',
            'status' => 1,
        ]);

        // Explicitly create Staff user
        User::create([
            'nama_lengkap' => 'Staff User',
            'email' => 'staff@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'staff',
            'status' => 1,
        ]);

        // Call other seeders
        $this->call([
            CategorySeeder::class,
            FinancialTransactionSeeder::class,
        ]);
    }
}
