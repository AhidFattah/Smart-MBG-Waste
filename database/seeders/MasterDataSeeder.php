<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class MasterDataSeeder extends Seeder
{
    public function run()
    {
        // 1. Mengisi Data Master Sekolah
        DB::table('schools')->insert([
            [
                'id' => 1,
                'school_code' => 'SCH001',
                'name' => 'SDN Merdeka 01 Malang',
                'address' => 'Jl. Merdeka No. 1, Klojen, Kota Malang',
                'total_students' => 500,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'school_code' => 'SCH002',
                'name' => 'SMP Negeri 01 Malang',
                'address' => 'Jl. Lawu No. 12, Klojen, Kota Malang',
                'total_students' => 750,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        // 2. Mengisi Data Akun Demo untuk Setiap Role Aktor
        DB::table('users')->insert([
            [
                'name' => 'Ahmad Admin Pusat',
                'email' => 'admin@mbg.com',
                'password' => Hash::make('password123'),
                'role' => 'admin_pusat',
                'school_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Budi Petugas Dapur',
                'email' => 'dapur@mbg.com',
                'password' => Hash::make('password123'),
                'role' => 'petugas_dapur',
                'school_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Siti Petugas SDN 01',
                'email' => 'petugas.sdn01@mbg.com',
                'password' => Hash::make('password123'),
                'role' => 'petugas_sekolah',
                'school_id' => 1, // Terikat ke SDN Merdeka 01
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Hadi Kepsek SDN 01',
                'email' => 'kepsek.sdn01@mbg.com',
                'password' => Hash::make('password123'),
                'role' => 'kepala_sekolah',
                'school_id' => 1, // Terikat ke SDN Merdeka 01
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        // 3. Mengisi Data Master Variasi Menu MBG
        DB::table('menus')->insert([
            [
                'id' => 1,
                'menu_code' => 'MN001',
                'menu_name' => 'Nasi Ayam Saus Tiram + Tumis Capcay',
                'calories' => 650,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'menu_code' => 'MN002',
                'menu_name' => 'Nasi Goreng Hongkong + Telur Mata Sapi',
                'calories' => 580,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}