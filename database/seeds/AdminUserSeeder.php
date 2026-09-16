<?php

use Illuminate\Database\Seeder;
use App\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        User::updateOrCreate(
            ['email' => 'admin@recruitment.local'],
            ['name' => 'HRD Admin', 'password' => Hash::make('admin123')]
        );
    }
}
