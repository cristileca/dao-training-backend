<?php

namespace Database\Seeders;

use App\Models\Package;
use Illuminate\Database\Seeder;


class PackagesSeeder extends Seeder
{
    public function run(): void
    {
        Package::factory()->count(6)->create();
    }

}
