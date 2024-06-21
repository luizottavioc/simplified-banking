<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            'name' => 'admin',
            'cpf' => null,
            'cnpj' => null,
            'email' => 'admin@admin.com',
            'password' => bcrypt(env('ADMIN_PASSWORD')),
            'user_type_id' => 1,
            'wallet' => 0
        ]);

        DB::table('users')->insert([
            'name' => 'teller',
            'cpf' => null,
            'cnpj' => null,
            'email' => 'teller@teller.com',
            'password' => bcrypt(env('TELLER_PASSWORD')),
            'user_type_id' => 2,
            'wallet' => 0
        ]);
    }
}
