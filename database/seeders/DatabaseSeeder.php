<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Mengambil data dari file .env
        $adminEmail = env('ADMIN_EMAIL', 'bintangkelaci@gmail.com'); 
        $adminPassword = env('ADMIN_PASSWORD', 'kuisinteraktif2026'); 

        if(User::where('email', $adminEmail)->doesntExist()) {
            User::updateOrCreate([
                'email' => $adminEmail,
            ], [
                'name' => 'Admin GenBI',
                'password' => Hash::make($adminPassword),
            ]);
        }
    }
}