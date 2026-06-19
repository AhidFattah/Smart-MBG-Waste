<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class MasterDataSeeder extends Seeder
{
    public function run()
    {
        // Disable foreign key check
        DB::statement('SET FOREIGN_KEY_CHECKS = 0;');
        
        DB::table('settings')->truncate();
        DB::table('activity_logs')->truncate();
        DB::table('notifications')->truncate();
        DB::table('recommendations')->truncate();
        DB::table('food_wastes')->truncate();
        DB::table('food_waste_logs')->truncate();
        DB::table('distributions')->truncate();
        DB::table('schools')->truncate();
        DB::table('menus')->truncate();
        DB::table('users')->truncate();
        DB::table('inventories')->truncate();
        DB::table('inventory_logs')->truncate();
        DB::table('suppliers')->truncate();
        DB::table('roles')->truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS = 1;');

        // 1. Roles
        DB::table('roles')->insert([
            ['id' => 1, 'role_name' => 'admin_pusat', 'display_name' => 'Super Admin', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'role_name' => 'petugas_dapur', 'display_name' => 'Admin Dapur', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'role_name' => 'petugas_sekolah', 'display_name' => 'Petugas Sekolah', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'role_name' => 'kepala_sekolah', 'display_name' => 'Viewer', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 2. Suppliers
        DB::table('suppliers')->insert([
            ['id' => 1, 'name' => 'Koperasi Tani Makmur', 'contact_name' => 'Pak Joko', 'phone' => '08123456789', 'address' => 'Kec. Karangploso, Malang', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'Peternakan Ayam Sentosa', 'contact_name' => 'Bu Sri', 'phone' => '08234567890', 'address' => 'Kec. Pakis, Malang', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'name' => 'Distributor Beras Sejahtera', 'contact_name' => 'Pak Budi', 'phone' => '08345678901', 'address' => 'Kec. Kepanjen, Malang', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 3. Schools
        DB::table('schools')->insert([
            ['id' => 1, 'school_code' => 'SCH001', 'name' => 'SDN 1 Arjosari', 'address' => 'Jl. Raden Intan No. 1, Arjosari, Kota Malang', 'total_students' => 180, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'school_code' => 'SCH002', 'name' => 'SDN 3 Blimbing', 'address' => 'Jl. Borobudur No. 12, Blimbing, Kota Malang', 'total_students' => 220, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'school_code' => 'SCH003', 'name' => 'SDN 2 Mojolangu', 'address' => 'Jl. Sudimoro No. 5, Lowokwaru, Kota Malang', 'total_students' => 150, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'school_code' => 'SCH004', 'name' => 'SDN 1 Dinoyo', 'address' => 'Jl. MT Haryono No. 3, Lowokwaru, Kota Malang', 'total_students' => 200, 'created_at' => now(), 'updated_at' => now()]
        ]);

        // 4. Users
        DB::table('users')->insert([
            [
                'id' => 1,
                'name' => 'Ahmad Super Admin',
                'email' => 'admin@mbg.com',
                'password' => Hash::make('password123'),
                'role' => 'admin_pusat',
                'role_id' => 1,
                'school_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Budi Admin Dapur',
                'email' => 'dapur@mbg.com',
                'password' => Hash::make('password123'),
                'role' => 'petugas_dapur',
                'role_id' => 2,
                'school_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name' => 'Siti Petugas SDN 01',
                'email' => 'petugas.sdn01@mbg.com',
                'password' => Hash::make('password123'),
                'role' => 'petugas_sekolah',
                'role_id' => 3,
                'school_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'name' => 'Hadi Viewer / Kepsek',
                'email' => 'kepsek.sdn01@mbg.com',
                'password' => Hash::make('password123'),
                'role' => 'kepala_sekolah',
                'role_id' => 4,
                'school_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        // 5. Menus
        DB::table('menus')->insert([
            ['id' => 1, 'menu_code' => 'MN001', 'menu_name' => 'Menu A: Nasi Putih + Ayam Karage + Sop Wortel', 'calories' => 650, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'menu_code' => 'MN002', 'menu_name' => 'Menu B: Nasi Kuning + Semur Daging + Tumis Buncis', 'calories' => 580, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'menu_code' => 'MN003', 'menu_name' => 'Menu C: Nasi Putih + Kakap Fillet + Capcay Ayam', 'calories' => 610, 'created_at' => now(), 'updated_at' => now()]
        ]);

        // 6. Inventories
        DB::table('inventories')->insert([
            [
                'id' => 1,
                'nama_bahan' => 'Beras Pandan Wangi',
                'kategori' => 'Beras',
                'stok' => 850,
                'satuan' => 'Kg',
                'tanggal_masuk' => '2026-06-10',
                'tanggal_kedaluwarsa' => '2027-06-10',
                'status_stok' => 'Aman',
                'supplier_id' => 3,
                'harga' => 14000.00,
                'lokasi_penyimpanan' => 'Rak A-1',
                'barcode' => '8991234567890',
                'qr_code' => 'INV-BERAS-PW-01',
                'foto_bahan' => null,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 2,
                'nama_bahan' => 'Daging Ayam Fillet',
                'kategori' => 'Lauk',
                'stok' => 120,
                'satuan' => 'Kg',
                'tanggal_masuk' => '2026-06-15',
                'tanggal_kedaluwarsa' => '2026-06-25', // Expired hampir kadaluarsa H-7 (saat ini 2026-06-18)
                'status_stok' => 'Aman',
                'supplier_id' => 2,
                'harga' => 36000.00,
                'lokasi_penyimpanan' => 'Cold Storage B-1',
                'barcode' => '8991234567891',
                'qr_code' => 'INV-AYAM-F-02',
                'foto_bahan' => null,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 3,
                'nama_bahan' => 'Sayur Sawi & Capcay Mix',
                'kategori' => 'Sayur',
                'stok' => 12, // Stok menipis
                'satuan' => 'Kg',
                'tanggal_masuk' => '2026-06-16',
                'tanggal_kedaluwarsa' => '2026-06-21', // Kadaluarsa dekat
                'status_stok' => 'Menipis',
                'supplier_id' => 1,
                'harga' => 8000.00,
                'lokasi_penyimpanan' => 'Chiller C-1',
                'barcode' => '8991234567892',
                'qr_code' => 'INV-SAYUR-M-03',
                'foto_bahan' => null,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 4,
                'nama_bahan' => 'Minyak Goreng Sawit',
                'kategori' => 'Lainnya',
                'stok' => 0, // Habis
                'satuan' => 'Liter',
                'tanggal_masuk' => '2026-05-01',
                'tanggal_kedaluwarsa' => '2027-05-01',
                'status_stok' => 'Habis',
                'supplier_id' => 1,
                'harga' => 16500.00,
                'lokasi_penyimpanan' => 'Rak D-2',
                'barcode' => '8991234567893',
                'qr_code' => 'INV-MINYAK-S-04',
                'foto_bahan' => null,
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);

        // 7. Seed Inventory Logs
        DB::table('inventory_logs')->insert([
            ['inventory_id' => 1, 'tipe' => 'masuk', 'jumlah' => 1000, 'sisa_stok_saat_itu' => 1000, 'harga_satuan' => 14000.00, 'keterangan' => 'Stok awal beras', 'created_at' => '2026-06-10 08:00:00'],
            ['inventory_id' => 1, 'tipe' => 'keluar', 'jumlah' => 150, 'sisa_stok_saat_itu' => 850, 'harga_satuan' => 14000.00, 'keterangan' => 'Pengurangan untuk Dapur', 'created_at' => '2026-06-16 10:00:00'],
            ['inventory_id' => 2, 'tipe' => 'masuk', 'jumlah' => 150, 'sisa_stok_saat_itu' => 150, 'harga_satuan' => 36000.00, 'keterangan' => 'Stok awal ayam', 'created_at' => '2026-06-15 09:00:00'],
            ['inventory_id' => 2, 'tipe' => 'keluar', 'jumlah' => 30, 'sisa_stok_saat_itu' => 120, 'harga_satuan' => 36000.00, 'keterangan' => 'Deduction distribusi', 'created_at' => '2026-06-17 11:00:00'],
            ['inventory_id' => 3, 'tipe' => 'masuk', 'jumlah' => 20, 'sisa_stok_saat_itu' => 20, 'harga_satuan' => 8000.00, 'keterangan' => 'Sayur masuk', 'created_at' => '2026-06-16 07:30:00'],
            ['inventory_id' => 3, 'tipe' => 'keluar', 'jumlah' => 8, 'sisa_stok_saat_itu' => 12, 'harga_satuan' => 8000.00, 'keterangan' => 'Dipakai masak menu A', 'created_at' => '2026-06-17 10:00:00'],
        ]);

        // 8. Seed 7 Days of Historical Data for distributions and food waste (to make AI Insight/DSS recommendations realistic)
        $sekolahs = [
            1 => ['nama' => 'SDN 1 Arjosari', 'siswa' => 180, 'porsi_avg' => 185],
            2 => ['nama' => 'SDN 3 Blimbing', 'siswa' => 220, 'porsi_avg' => 226],
            3 => ['nama' => 'SDN 2 Mojolangu', 'siswa' => 150, 'porsi_avg' => 154],
            4 => ['nama' => 'SDN 1 Dinoyo', 'siswa' => 200, 'porsi_avg' => 206]
        ];

        $kendaraans = ['Mobil Box A', 'Motor Roda Tiga B', 'Cater Box C', 'Mobil Kargo D'];
        $petugass = ['Joni Setiawan', 'Bambang Tri', 'Indra Setia', 'Agus Roni'];
        
        $currentDate = Carbon::parse('2026-06-18');
        
        // Simulasikan 7 hari terakhir (dari 11 Juni s/d 17 Juni)
        for ($i = 7; $i >= 1; $i--) {
            $tanggal = $currentDate->copy()->subDays($i)->toDateString();
            $menuId = (($i % 3) + 1); // Rotasi menu 1, 2, 3

            foreach ($sekolahs as $schoolId => $sch) {
                // Tambah cadangan 3%
                $siswaHadir = rand($sch['siswa'] - 15, $sch['siswa']);
                $totalPorsi = ceil($siswaHadir + ($siswaHadir * 0.03));
                
                // Masukkan distribusi
                $distId = DB::table('distributions')->insertGetId([
                    'school_id' => $schoolId,
                    'menu_id' => $menuId,
                    'total_porsi_dikirim' => $totalPorsi,
                    'tanggal_distribusi' => $tanggal,
                    'status_pengiriman' => 'Diterima',
                    'jumlah_siswa_hadir' => $siswaHadir,
                    'persentase_cadangan' => 3.00,
                    'kendaraan_distribusi' => $kendaraans[$schoolId - 1],
                    'petugas_distribusi' => $petugass[$schoolId - 1],
                    'waktu_distribusi' => '09:30',
                    'qr_code_surat_jalan' => 'QR-DIST-' . $tanggal . '-' . $schoolId,
                    'waktu_diterima' => Carbon::parse($tanggal . ' 10:15:00'),
                    'penerima_nama' => 'Petugas ' . $sch['nama'],
                    'created_at' => Carbon::parse($tanggal . ' 08:00:00'),
                    'updated_at' => Carbon::parse($tanggal . ' 10:30:00'),
                ]);

                // Simulasi sisa makanan per sekolah
                // SDN 3 Blimbing (school_id = 2) dibuat memiliki waste tinggi (> 15% secara konsisten)
                // SDN 1 Arjosari (school_id = 1) dibuat sedang (11-13%)
                // SDN 2 Mojolangu (school_id = 3) dibuat efisien / sedikit (< 5%)
                if ($schoolId == 2) { // Boros
                    $qtyHabis = ceil($totalPorsi * 0.80);
                    $qtySebagian = ceil($totalPorsi * 0.08);
                    $qtyTidakHabis = $totalPorsi - $qtyHabis - $qtySebagian;
                    $beratWaste = round(($qtyTidakHabis + ($qtySebagian * 0.5)) * 0.35, 2); // 350g per porsi sisa
                    $wastePercent = round((($qtyTidakHabis + ($qtySebagian * 0.5)) / $totalPorsi) * 100, 2);
                    $kategori = 'Tinggi';
                    $penyebab = 'Rasa Kurang Cocok';
                } elseif ($schoolId == 3) { // Efisien
                    $qtyHabis = ceil($totalPorsi * 0.96);
                    $qtySebagian = ceil($totalPorsi * 0.03);
                    $qtyTidakHabis = $totalPorsi - $qtyHabis - $qtySebagian;
                    $beratWaste = round(($qtyTidakHabis + ($qtySebagian * 0.5)) * 0.35, 2);
                    $wastePercent = round((($qtyTidakHabis + ($qtySebagian * 0.5)) / $totalPorsi) * 100, 2);
                    $kategori = 'Sedikit';
                    $penyebab = 'Porsi Terlalu Banyak';
                } else { // Normal/Sedang
                    $qtyHabis = ceil($totalPorsi * 0.88);
                    $qtySebagian = ceil($totalPorsi * 0.06);
                    $qtyTidakHabis = $totalPorsi - $qtyHabis - $qtySebagian;
                    $beratWaste = round(($qtyTidakHabis + ($qtySebagian * 0.5)) * 0.35, 2);
                    $wastePercent = round((($qtyTidakHabis + ($qtySebagian * 0.5)) / $totalPorsi) * 100, 2);
                    $kategori = $wastePercent > 10 ? 'Sedang' : 'Sedikit';
                    $penyebab = 'Jam Makan Singkat';
                }

                // Insert ke food_waste_logs (lama)
                DB::table('food_waste_logs')->insert([
                    'distribution_id' => $distId,
                    'qty_habis' => $qtyHabis,
                    'qty_sebagian' => $qtySebagian,
                    'qty_tidak_habis' => $qtyTidakHabis,
                    'indeks_waste' => $wastePercent,
                    'rekomendasi_dss' => $wastePercent > 10.0 ? 'REDUKSI_PASOKAN' : 'PASOKAN_OPTIMAL',
                    'faktor_penyebab' => $penyebab,
                    'created_at' => Carbon::parse($tanggal . ' 13:00:00'),
                    'updated_at' => Carbon::parse($tanggal . ' 13:00:00'),
                ]);

                // Insert ke food_wastes (baru)
                DB::table('food_wastes')->insert([
                    'distribution_id' => $distId,
                    'berat_sisa_makanan' => $beratWaste,
                    'jumlah_sisa_porsi' => ceil($qtyTidakHabis + ($qtySebagian * 0.5)),
                    'persentase_waste' => $wastePercent,
                    'penyebab_waste' => $penyebab,
                    'kategori_waste' => $kategori,
                    'created_at' => Carbon::parse($tanggal . ' 13:00:00'),
                    'updated_at' => Carbon::parse($tanggal . ' 13:00:00'),
                ]);
            }
        }

        // 9. Recommendations (AI DSS)
        DB::table('recommendations')->insert([
            [
                'tipe' => 'portion',
                'school_id' => 2,
                'message' => 'Sekolah SDN 3 Blimbing memiliki rata-rata food waste 16.4% selama 7 hari terakhir (Ambatan: >15%). Rekomendasi: Kurangi kuota porsi distribusi berikutnya sebesar 10% untuk meminimalkan pemborosan.',
                'created_at' => now()
            ],
            [
                'tipe' => 'portion',
                'school_id' => 3,
                'message' => 'Sekolah SDN 2 Mojolangu memiliki rata-rata food waste 3.2% selama 7 hari terakhir (Ambatan: <5%). Rekomendasi: Kuota porsi dapat dipertahankan atau sedikit ditingkatkan karena tingkat konsumsi sangat tinggi.',
                'created_at' => now()
            ],
            [
                'tipe' => 'menu',
                'school_id' => null,
                'message' => 'Menu B (Nasi Kuning + Semur Daging + Tumis Buncis) memiliki tingkat food waste terendah secara rata-rata nasional (4.8%). Disarankan memperbanyak frekuensi penyajian Menu B.',
                'created_at' => now()
            ]
        ]);

        // 10. Notifications
        DB::table('notifications')->insert([
            ['tipe' => 'warning', 'message' => '⚠️ STOK MENIPIS: Sayur Sawi & Capcay Mix tersisa 12 Kg (di bawah batas minimum 20 Kg)!', 'read_at' => null, 'created_at' => now()],
            ['tipe' => 'danger', 'message' => '🚨 STOK HABIS: Minyak Goreng Sawit kosong. Harap hubungi supplier segera!', 'read_at' => null, 'created_at' => now()],
            ['tipe' => 'warning', 'message' => '🕒 KADALUARSA H-7: Daging Ayam Fillet di Cold Storage akan kedaluwarsa dalam waktu 7 hari (25 Juni 2026).', 'read_at' => null, 'created_at' => now()],
            ['tipe' => 'info', 'message' => '✅ SISTEM: Sinkronisasi data distribusi tanggal 17 Juni 2026 berhasil.', 'read_at' => now(), 'created_at' => now()->subHours(5)],
        ]);

        // 11. Activity Logs
        DB::table('activity_logs')->insert([
            ['user_id' => 1, 'aktivitas' => 'LOG_IN', 'deskripsi' => 'Super Admin Ahmad berhasil login ke dalam sistem.', 'ip_address' => '127.0.0.1', 'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', 'created_at' => now()->subHours(2)],
            ['user_id' => 2, 'aktivitas' => 'UPDATE_STOCK', 'deskripsi' => 'Petugas Dapur Budi memperbarui stok Sayur Sawi & Capcay Mix sebanyak 20 Kg.', 'ip_address' => '127.0.0.1', 'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', 'created_at' => now()->subHour()],
        ]);

        // 12. Settings
        DB::table('settings')->insert([
            ['key' => 'app_name', 'value' => 'Smart MBG Waste', 'group' => 'umum', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'threshold_waste_high', 'value' => '15', 'group' => 'dss', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'threshold_waste_low', 'value' => '5', 'group' => 'dss', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'buffer_percentage', 'value' => '3', 'group' => 'distribusi', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'default_lang', 'value' => 'id', 'group' => 'umum', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'theme', 'value' => 'dark', 'group' => 'tampilan', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}