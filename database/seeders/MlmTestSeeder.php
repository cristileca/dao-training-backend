<?php


namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class MlmTestSeeder extends Seeder
{
    public function run(): void
    {
        $root = User::create([
            'id' => (string)Str::uuid(),
            'name' => 'root_user',
            'email' => 'root@example.com',
            'password' => Hash::make('password'),
            'referral_id' => null,
        ]);

        $previousLevelUsers = [$root];

        for ($level = 1; $level <= 9; $level++) {

            $currentLevelUsers = [];

            foreach ($previousLevelUsers as $parent) {

                for ($i = 1; $i <= 3; $i++) {

                    $user = User::create([
                        'id' => (string)Str::uuid(),
                        'name' => "level{$level}_user_{$i}",
                        'email' => "level{$level}_user_{$i}_" . Str::random(5) . "@example.com",
                        'password' => Hash::make('password'),
                        'referral_id' => $parent->id,
                    ]);

                    $currentLevelUsers[] = $user;
                }
            }

            $previousLevelUsers = $currentLevelUsers;
        }
    }
}
