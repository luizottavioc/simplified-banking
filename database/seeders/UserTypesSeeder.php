<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('user_types')->insert([
            'type' => 'admin',
        ]);

        DB::table('user_types')->insert([
            'type' => 'teller',
        ]);

        DB::table('user_types')->insert([
            'type' => 'merchant',
        ]);

        DB::table('user_types')->insert([
            'type' => 'usual',
        ]);
    }
}
