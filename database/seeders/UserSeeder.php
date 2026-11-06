<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            ['name' => 'Petugas Arsip', 'email' => 'petugas@earsip.test', 'password' => Hash::make('password'), 'role' => 'petugas', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Kepala Bagian Kredit', 'email' => 'pimpinan@earsip.test', 'password' => Hash::make('password'), 'role' => 'pimpinan', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
