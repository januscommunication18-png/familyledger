<?php

namespace Database\Seeders;

use App\Models\Backoffice\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class BackofficeAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Admin::updateOrCreate(
            ['email' => 'admin@familyledger.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
    }
}
