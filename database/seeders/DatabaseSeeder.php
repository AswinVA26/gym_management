<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * The first account ever created is the platform super admin. If a super
     * admin already exists, seeding is skipped so the fleet is left untouched.
     */
    public function run(): void
    {
        if (User::where('role', User::ROLE_SUPER_ADMIN)->exists()) {
            $this->command?->warn('A super admin already exists; skipping platform seed.');

            return;
        }

        User::create([
            'name' => env('ADMIN_NAME', 'Platform Owner'),
            'email' => env('ADMIN_EMAIL', 'owner@gymhub.test'),
            'password' => Hash::make(env('ADMIN_PASSWORD', 'ChangeMe123!')),
            'role' => User::ROLE_SUPER_ADMIN,
            'status' => true,
            'email_verified_at' => Carbon::now(),
        ]);
    }
}
