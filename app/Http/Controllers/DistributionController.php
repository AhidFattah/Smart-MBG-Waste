<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\ActivityLogger;
use App\Models\Distribution;
use Carbon\Carbon;

class DistributionController extends Controller
{
    // A. LIST TRANSAKSI DISTRIBUSI
    public function index(Request $request)
    {
        $distributions = DB::table('distributions')
            ->join('schools', 'distributions.school_id', '=', 'schools.id')
            ->join('menus', 'distributions.menu_id', '=', 'menus.id')
            ->select('distributions.*', 'schools.name as nama_sekolah', 'schools.total_students as kapasitas_sekolah', 'menus.menu_name as nama_menu')
            ->orderBy('distributions.tanggal_distribusi', 'desc')
            ->orderBy('distributions.id', 'desc')
            ->get();

        $schools = DB::table('schools')->get();
        $menus = DB::table('menus')->get();

        return view('distribusi.index', compact('distributions', 'schools', 'menus'));
    }

    // B. SIMPAN DISTRIBUSI BARU (FIFO REDUCTION TRIGGERED)
    public function store(Request $request)
    {
        $request->validate([
            'school_id' => 'required|integer',
            'menu_id' => 'required|integer',
            'jumlah_siswa_hadir' => 'required|integer|min:1',
            'persentase_cadangan' => 'required|numeric|min:0',
            'tanggal_distribusi' => 'required|date',
            'kendaraan_distribusi' => 'nullable|string',
            'petugas_distribusi' => 'nullable|string',
            'waktu_distribusi' => 'nullable|string',
        ]);

        $schoolId = (int) $request->input('school_id');
        $menuId = (int) $request->input('menu_id');
        $siswaHadir = (int) $request->input('jumlah_siswa_hadir');
        $cadanganPercent = (float) $request->input('persentase_cadangan');

        // Hitung kuota porsi otomatis
        // Rumus: Porsi = Siswa Hadir + (Siswa Hadir * cadanganPercent / 100)
        $totalPorsi = (int) ceil($siswaHadir + ($siswaHadir * ($cadanganPercent / 100)));

        // Mulai database transaction untuk menjamin data integrity
        DB::beginTransaction();
        try {
            // Ambil detail nama sekolah dan menu
            $school = DB::table('schools')->where('id', $schoolId)->first();
            $menu = DB::table('menus')->where('id', $menuId)->first();

            // 1. GENERATE QR CODE DISTRIBUSI
            $qrCode = 'QR-DIST-' . date('Ymd') . '-' . $schoolId . '-' . rand(100, 999);

            // 2. INSERT KE TABLE DISTRIBUSI
            $distId = DB::table('distributions')->insertGetId([
                'school_id' => $schoolId,
                'menu_id' => $menuId,
                'total_porsi_dikirim' => $totalPorsi,
                'tanggal_distribusi' => $request->input('tanggal_distribusi'),
                'status_pengiriman' => 'Diproses',
                'jumlah_siswa_hadir' => $siswaHadir,
                'persentase_cadangan' => $cadanganPercent,
                'kendaraan_distribusi' => $request->input('kendaraan_distribusi') ?: 'Kurir Kargo A',
                'petugas_distribusi' => $request->input('petugas_distribusi') ?: 'Petugas Dapur',
                'waktu_distribusi' => $request->input('waktu_distribusi') ?: '08:30',
                'qr_code_surat_jalan' => $qrCode,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 3. FIFO STOK DEDUCTION LOGIC
            // Definisikan bahan yang dibutuhkan per porsi berdasarkan menu yang dipilih
            $requirements = [];
            if ($menuId == 1) { // Menu A
                $requirements = [
                    'Beras' => ['qty' => 0.15, 'nama' => 'Beras Pandan Wangi'], 
                    'Lauk'  => ['qty' => 0.10, 'nama' => 'Daging Ayam Fillet'],
                    'Sayur' => ['qty' => 0.05, 'nama' => 'Sayur Sawi & Capcay Mix']
                ];
            } elseif ($menuId == 2) { // Menu B
                $requirements = [
                    'Beras' => ['qty' => 0.15, 'nama' => 'Beras Pandan Wangi'],
                    'Lauk'  => ['qty' => 0.12, 'nama' => 'Daging Ayam Fillet']
                ];
            } else { // Menu C
                $requirements = [
                    'Beras' => ['qty' => 0.15, 'nama' => 'Beras Pandan Wangi'],
                    'Lauk'  => ['qty' => 0.08, 'nama' => 'Daging Ayam Fillet'],
                    'Sayur' => ['qty' => 0.08, 'nama' => 'Sayur Sawi & Capcay Mix']
                ];
            }

            // Jalankan FIFO deduction untuk masing-masing kebutuhan bahan
            foreach ($requirements as $kategori => $req) {
                $totalKebutuhan = $totalPorsi * $req['qty']; // dalam Kg / satuan bahan
                
                // Cari batch bahan di gudang yang kategorinya cocok, urutkan berdasarkan FIFO (tanggal expired terdekat)
                $batches = DB::table('inventories')
                    ->where('kategori', $kategori)
                    ->where('stok', '>', 0)
                    ->orderBy('tanggal_kedaluwarsa', 'asc')
                    ->orderBy('tanggal_masuk', 'asc')
                    ->get();

                $sisaDibutuhkan = $totalKebutuhan;

                foreach ($batches as $batch) {
                    if ($sisaDibutuhkan <= 0) break;

                    if ($batch->stok >= $sisaDibutuhkan) {
                        // Batch ini cukup untuk memenuhi semua sisa kebutuhan
                        $stokBaru = $batch->stok - $sisaDibutuhkan;
                        
                        DB::table('inventories')->where('id', $batch->id)->update([
                            'stok' => $stokBaru,
                            'status_stok' => $stokBaru == 0 ? 'Habis' : ($stokBaru < 20 ? 'Menipis' : 'Aman'),
                            'updated_at' => now()
                        ]);

                        // Catat log pengeluaran
                        DB::table('inventory_logs')->insert([
                            'inventory_id' => $batch->id,
                            'tipe' => 'keluar',
                            'jumlah' => $sisaDibutuhkan,
                            'sisa_stok_saat_itu' => $stokBaru,
                            'harga_satuan' => $batch->harga,
                            'keterangan' => 'FIFO: Distribusi ' . $totalPorsi . ' porsi ke ' . $school->name,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);

                        // Cek and trigger notifikasi jika stok menipis/habis
                        if ($stokBaru < 20) {
                            DB::table('notifications')->insert([
                                'tipe' => $stokBaru == 0 ? 'danger' : 'warning',
                                'message' => ($stokBaru == 0 ? '🚨 STOK HABIS: ' : '⚠️ STOK MENIPIS: ') . $batch->nama_bahan . ' tersisa ' . $stokBaru . ' ' . $batch->satuan,
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);
                        }

                        $sisaDibutuhkan = 0;
                    } else {
                        // Batch ini tidak cukup, habiskan stok batch ini dan cari batch berikutnya
                        $jumlahDiambil = $batch->stok;
                        $sisaDibutuhkan -= $jumlahDiambil;

                        DB::table('inventories')->where('id', $batch->id)->update([
                            'stok' => 0,
                            'status_stok' => 'Habis',
                            'updated_at' => now()
                        ]);

                        // Catat log pengeluaran
                        DB::table('inventory_logs')->insert([
                            'inventory_id' => $batch->id,
                            'tipe' => 'keluar',
                            'jumlah' => $jumlahDiambil,
                            'sisa_stok_saat_itu' => 0,
                            'harga_satuan' => $batch->harga,
                            'keterangan' => 'FIFO: Distribusi ' . $totalPorsi . ' porsi ke ' . $school->name . ' (Batch habis)',
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);

                        DB::table('notifications')->insert([
                            'tipe' => 'danger',
                            'message' => '🚨 STOK HABIS: Batch ' . $batch->nama_bahan . ' telah habis digunakan untuk distribusi!',
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                }

                // Jika kebutuhan bahan masih tersisa dan batch habis, system mencatat log warning
                if ($sisaDibutuhkan > 0) {
                    DB::table('notifications')->insert([
                        'tipe' => 'danger',
                        'message' => '🚨 WARNING GUDANG: Bahan ' . $req['nama'] . ' kurang ' . round($sisaDibutuhkan, 2) . ' Kg untuk pesanan distribusi ' . $school->name,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }

            // Catat log aktivitas
            ActivityLogger::log('CREATE_DISTRIBUTION', 'Membuat distribusi ' . $totalPorsi . ' porsi ' . $menu->menu_name . ' ke ' . $school->name);

            DB::commit();
            return redirect()->back()->with('success', 'Distribusi berhasil dibuat! Stok bahan berkurang otomatis (FIFO).');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    // C. UPDATE STATUS PENGIRIMAN
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status_pengiriman' => 'required|in:Diproses,Dalam_Perjalanan,Diterima,Gagal',
            'penerima_nama' => 'nullable|string',
        ]);

        $status = $request->input('status_pengiriman');
        $penerima = $request->input('penerima_nama');
        $waktuDiterima = $status == 'Diterima' ? now() : null;

        $dist = DB::table('distributions')->where('id', $id)->first();
        if (!$dist) {
            return response()->json(['success' => false, 'message' => 'Distribusi tidak ditemukan'], 404);
        }

        DB::table('distributions')->where('id', $id)->update([
            'status_pengiriman' => $status,
            'penerima_nama' => $penerima,
            'waktu_diterima' => $waktuDiterima,
            'updated_at' => now()
        ]);

        ActivityLogger::log('UPDATE_DISTRIBUTION_STATUS', 'Mengubah status distribusi #' . $id . ' menjadi ' . $status);

        // Jika distribusi gagal, trigger notifikasi
        if ($status == 'Gagal') {
            DB::table('notifications')->insert([
                'tipe' => 'danger',
                'message' => '🚨 DISTRIBUSI GAGAL: Pengiriman makanan ke sekolah ID ' . $dist->school_id . ' terganggu/gagal!',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        return redirect()->back()->with('success', 'Status pengiriman distribusi berhasil diperbarui!');
    }

    // D. CETAK SURAT JALAN
    public function printSuratJalan($id)
    {
        $distribution = DB::table('distributions')
            ->join('schools', 'distributions.school_id', '=', 'schools.id')
            ->join('menus', 'distributions.menu_id', '=', 'menus.id')
            ->select('distributions.*', 'schools.name as nama_sekolah', 'schools.address as alamat_sekolah', 'schools.total_students as kapasitas_sekolah', 'menus.menu_name as nama_menu')
            ->where('distributions.id', $id)
            ->first();

        if (!$distribution) {
            abort(404, 'Data surat jalan tidak ditemukan.');
        }

        return view('distribusi.surat_jalan', compact('distribution'));
    }

    // E. DELETE DISTRIBUSI
    public function destroy($id)
    {
        $dist = DB::table('distributions')->where('id', $id)->first();
        if ($dist) {
            ActivityLogger::log('DELETE_DISTRIBUTION', 'Menghapus catatan distribusi #' . $id);
            DB::table('distributions')->where('id', $id)->delete();
            return redirect()->back()->with('success', 'Data distribusi berhasil dihapus.');
        }
        return redirect()->back()->with('error', 'Data tidak ditemukan.');
    }
}
