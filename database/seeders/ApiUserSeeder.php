<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class ApiUserSeeder extends Seeder
{
    /**
     * Seed the API user used to issue access tokens.
     */
    public function run(): void
    {
        $email = config('api.user.email');
        $password = config('api.user.password');

        if (app()->isProduction() && (blank($email) || blank($password))) {
            $this->command?->warn('API_USER_EMAIL and API_USER_PASSWORD must be defined to seed the API user.');

            return;
        }

        $user = User::query()->firstOrCreate(
            ['email' => $email ?? 'admin@begento.test'],
            [
                'name' => config('api.user.name'),
                'password' => $password ?? 'password',
            ],
        );

        $this->command?->info("API user ready: {$user->email}");
    }
}
