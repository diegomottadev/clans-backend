<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class UserSeed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::updateOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'admin',
                'role' => 'admin',
                'email_verified_at' => Carbon::now(),
                'password' => bcrypt('admin'),
            ]
        );

        User::updateOrCreate(
            ['email' => 'user@user.com'],
            [
                'name' => 'user',
                'role' => 'user',
                'email_verified_at' => Carbon::now(),
                'password' => bcrypt('user'),
            ]
        );
    }
}
