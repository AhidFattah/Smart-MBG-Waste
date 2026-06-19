<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\ActivityLogger;
use Carbon\Carbon;

class FoodWasteController extends Controller
{
    // A. MENAMPILKAN FORM INPUT FOOD WASTE
    public function showInputForm()
    {
        $schools = DB::table('schools')->get();
        $menus = DB::table('menus')->get();
        
        // Ambil distribusi hari ini yang belum diisi food waste
        $distributions = DB::table('distributions')
            ->join('schools', 'distributions.school_id', '=', 'schools.id')
            ->join('menus', 'distributions.menu_id', '=', 'menus.id')
            ->leftJoin('food_wastes', 'distributions.id', '=', 'food_wastes.distribution_id')
            ->whereNull('food_wastes.id')
            ->select('distributions.*', 'schools.name as nama_sekolah', 'menus.menu_name as nama_menu')
            ->get();

        return view('sekolah.waste-input', compact('schools', 'menus', 'distributions'));
    }

    // B. PROSES SIMPAN DATA FOOD WASTE & DSS
    public function storeWasteLog(Request $request)
    {
        $request->validate([
            'school_id' => 'required|integer',
            'menu_id' => 'required|integer',
            'tanggal_distribusi' => 'required|date',
            'qty_habis' => 'required|integer|min:0',
            'qty_sebagian' => 'required|integer|min:0',
            'qty_tidak_habis' => 'required|integer|min:0',
            'berat_sisa_makanan' => 'required|numeric|min:0',
            'faktor_penyebab' => 'required|string',
        ]);

        $schoolId = (int) $request->input('school_id');
        $menuId = (int) $request->input('menu_id');
        $tanggal = $request->input('tanggal_distribusi');
        
        $qtyHabis = (int) $request->input('qty_habis');
        $qtySebagian = (int) $request->input('qty_sebagian');
        $qtyTidakHabis = (int) $request->input('qty_tidak_habis');
        $beratSisa = (float) $request->input('berat_sisa_makanan');
        $faktor = $request->input('faktor_penyebab');

        // Cari transaksi distribusi yang cocok
        $dist = DB::table('distributions')
            ->where('school_id', $schoolId)
            ->where('menu_id', $menuId)
            ->where('tanggal_distribusi', $tanggal)
            ->first();

        // Fallback: jika distribusi belum terbuat di dapur, buat distribusi otomatis dengan status Diterima
        if (!$dist) {
            $school = DB::table('schools')->where('id', $schoolId)->first();
            $totalPorsi = $school ? $school->total_students : 180;
            
            $distId = DB::table('distributions')->insertGetId([
                'school_id' => $schoolId,
                'menu_id' => $menuId,
                'total_porsi_dikirim' => $totalPorsi,
                'tanggal_distribusi' => $tanggal,
                'status_pengiriman' => 'Diterima',
                'jumlah_siswa_hadir' => $totalPorsi,
                'persentase_cadangan' => 3.00,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $totalPorsiDikirim = $totalPorsi;
        } else {
            $distId = $dist->id;
            $totalPorsiDikirim = $dist->total_porsi_dikirim;
        }

        // Kalkulasi persentase waste (sisa porsi sisa total + setengah sisa sebagian)
        $porsiSisaRiil = $qtyTidakHabis + ($qtySebagian * 0.5);
        
        // Anti zero-division
        if ($totalPorsiDikirim <= 0) {
            $totalPorsiDikirim = 180;
        }

        $persentaseWaste = round(($porsiSisaRiil / $totalPorsiDikirim) * 100, 2);
        if ($persentaseWaste > 100) $persentaseWaste = 100;
        if ($persentaseWaste < 0) $persentaseWaste = 0;

        // Tentukan kategori otomatis
        if ($persentaseWaste <= 10.00) {
            $kategori = 'Sedikit';
            $rekomendasiDss = 'PASOKAN_OPTIMAL';
        } elseif ($persentaseWaste <= 20.00) {
            $kategori = 'Sedang';
            $rekomendasiDss = 'PASOKAN_OPTIMAL';
        } else {
            $kategori = 'Tinggi';
            $rekomendasiDss = 'REDUKSI_PASOKAN';
        }

        DB::beginTransaction();
        try {
            // Cek apakah data waste untuk distribusi ini sudah diisi untuk menghindari double entry
            $exist = DB::table('food_wastes')->where('distribution_id', $distId)->first();
            if ($exist) {
                DB::table('food_wastes')->where('id', $exist->id)->update([
                    'berat_sisa_makanan' => $beratSisa,
                    'jumlah_sisa_porsi' => ceil($porsiSisaRiil),
                    'persentase_waste' => $persentaseWaste,
                    'penyebab_waste' => $faktor,
                    'kategori_waste' => $kategori,
                    'updated_at' => now(),
                ]);
            } else {
                // Simpan ke food_wastes (baru)
                DB::table('food_wastes')->insert([
                    'distribution_id' => $distId,
                    'berat_sisa_makanan' => $beratSisa,
                    'jumlah_sisa_porsi' => ceil($porsiSisaRiil),
                    'persentase_waste' => $persentaseWaste,
                    'penyebab_waste' => $faktor,
                    'kategori_waste' => $kategori,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Simpan ke food_waste_logs (lama) untuk kompatibilitas tampilan laporan
            $existLog = DB::table('food_waste_logs')->where('distribution_id', $distId)->first();
            if ($existLog) {
                DB::table('food_waste_logs')->where('id', $existLog->id)->update([
                    'qty_habis' => $qtyHabis,
                    'qty_sebagian' => $qtySebagian,
                    'qty_tidak_habis' => $qtyTidakHabis,
                    'indeks_waste' => $persentaseWaste,
                    'rekomendasi_dss' => $rekomendasiDss,
                    'faktor_penyebab' => $faktor,
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('food_waste_logs')->insert([
                    'distribution_id' => $distId,
                    'qty_habis' => $qtyHabis,
                    'qty_sebagian' => $qtySebagian,
                    'qty_tidak_habis' => $qtyTidakHabis,
                    'indeks_waste' => $persentaseWaste,
                    'rekomendasi_dss' => $rekomendasiDss,
                    'faktor_penyebab' => $faktor,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // ==========================================
            // AI RECOMMENDATION ENGINE (HISTORI MIN 7 HARI)
            // ==========================================
            // Hitung rata-rata waste sekolah ini selama 7 hari terakhir
            $avgWaste = DB::table('food_wastes')
                ->join('distributions', 'food_wastes.distribution_id', '=', 'distributions.id')
                ->where('distributions.school_id', $schoolId)
                ->where('distributions.tanggal_distribusi', '>=', Carbon::parse($tanggal)->subDays(7)->toDateString())
                ->avg('food_wastes.persentase_waste');

            $schoolName = DB::table('schools')->where('id', $schoolId)->value('name');

            // Generate rekomendasi dinamis
            if ($avgWaste > 15.00) {
                $recomMsg = "Rata-rata food waste di " . $schoolName . " tinggi (" . round($avgWaste, 1) . "%). Rekomendasi: Kurangi kuota porsi distribusi berikutnya sebesar 10% untuk meminimalkan sisa.";
                
                DB::table('recommendations')->updateOrInsert(
                    ['school_id' => $schoolId, 'tipe' => 'portion'],
                    ['message' => $recomMsg, 'created_at' => now(), 'updated_at' => now()]
                );
            } elseif ($avgWaste < 5.00) {
                $recomMsg = "Rata-rata food waste di " . $schoolName . " sangat rendah (" . round($avgWaste, 1) . "%). Rekomendasi: Porsi dapat dipertahankan atau ditingkatkan jika terdapat permintaan tambahan.";
                
                DB::table('recommendations')->updateOrInsert(
                    ['school_id' => $schoolId, 'tipe' => 'portion'],
                    ['message' => $recomMsg, 'created_at' => now(), 'updated_at' => now()]
                );
            }

            // Trigger notifikasi real-time jika sisa tinggi
            if ($persentaseWaste > 20.00) {
                DB::table('notifications')->insert([
                    'tipe' => 'danger',
                    'message' => '🚨 FOOD WASTE TINGGI: Indeks sisa makanan di ' . $schoolName . ' mencapai ' . $persentaseWaste . '% pada menu ' . $menuId . '!',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            ActivityLogger::log('INPUT_FOOD_WASTE', 'Menginput data food waste ' . $persentaseWaste . '% untuk ' . $schoolName);

            DB::commit();
            return redirect()->route('sekolah.waste.riwayat')->with('success', 'Data hasil audit konsumsi berhasil disinkronkan ke pusat data!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memproses data waste: ' . $e->getMessage());
        }
    }

    // C. RIWAYAT FOOD WASTE SEKOLAH LOKAL
    public function riwayatWaste()
    {
        $user = auth()->user();
        
        $query = DB::table('food_wastes')
            ->join('distributions', 'food_wastes.distribution_id', '=', 'distributions.id')
            ->join('schools', 'distributions.school_id', '=', 'schools.id')
            ->join('menus', 'distributions.menu_id', '=', 'menus.id')
            ->select('food_wastes.*', 'distributions.tanggal_distribusi', 'distributions.total_porsi_dikirim', 'schools.name as nama_sekolah', 'menus.menu_name as nama_menu');

        // Jika petugas sekolah, batasi hanya melihat sekolahnya sendiri
        if ($user->role == 'petugas_sekolah' && $user->school_id) {
            $query->where('distributions.school_id', $user->school_id);
        }

        $wastes = $query->orderBy('distributions.tanggal_distribusi', 'desc')->get();

        return view('sekolah.riwayat_waste', compact('wastes'));
    }

    // D. KELOLA & FILTER LAPORAN UTAMA
    public function showLaporan(Request $request)
    {
        $schoolId = $request->input('school_id');
        $menuId = $request->input('menu_id');
        $dateStart = $request->input('date_start');
        $dateEnd = $request->input('date_end');

        $query = DB::table('food_wastes')
            ->join('distributions', 'food_wastes.distribution_id', '=', 'distributions.id')
            ->join('schools', 'distributions.school_id', '=', 'schools.id')
            ->join('menus', 'distributions.menu_id', '=', 'menus.id')
            ->select(
                'food_wastes.*', 
                'distributions.tanggal_distribusi', 
                'distributions.total_porsi_dikirim', 
                'schools.name as nama_sekolah', 
                'menus.menu_name as nama_menu',
                'distributions.school_id',
                'distributions.menu_id'
            );

        if ($schoolId) {
            $query->where('distributions.school_id', $schoolId);
        }
        if ($menuId) {
            $query->where('distributions.menu_id', $menuId);
        }
        if ($dateStart && $dateEnd) {
            $query->whereBetween('distributions.tanggal_distribusi', [$dateStart, $dateEnd]);
        }

        $laporanData = $query->orderBy('distributions.tanggal_distribusi', 'desc')->get();
        $schools = DB::table('schools')->get();
        $menus = DB::table('menus')->get();

        // Siapkan data visual grafik
        $chartLabels = [];
        $chartData = [];
        foreach ($laporanData as $row) {
            $chartLabels[] = $row->nama_sekolah . ' (' . date('d/m', strtotime($row->tanggal_distribusi)) . ')';
            $chartData[] = $row->persentase_waste;
        }

        return view('laporan.index', compact('laporanData', 'schools', 'menus', 'schoolId', 'menuId', 'dateStart', 'dateEnd', 'chartLabels', 'chartData'));
    }

    // E. EXPORT LAPORAN EXCEL
    public function exportExcel(Request $request)
    {
        $schoolId = $request->input('school_id');
        $menuId = $request->input('menu_id');
        $dateStart = $request->input('date_start');
        $dateEnd = $request->input('date_end');

        $query = DB::table('food_wastes')
            ->join('distributions', 'food_wastes.distribution_id', '=', 'distributions.id')
            ->join('schools', 'distributions.school_id', '=', 'schools.id')
            ->join('menus', 'distributions.menu_id', '=', 'menus.id')
            ->select('food_wastes.*', 'distributions.tanggal_distribusi', 'distributions.total_porsi_dikirim', 'schools.name as nama_sekolah', 'menus.menu_name as nama_menu');

        if ($schoolId) $query->where('distributions.school_id', $schoolId);
        if ($menuId) $query->where('distributions.menu_id', $menuId);
        if ($dateStart && $dateEnd) $query->whereBetween('distributions.tanggal_distribusi', [$dateStart, $dateEnd]);

        $data = $query->orderBy('distributions.tanggal_distribusi', 'desc')->get();

        $filename = "laporan_food_waste_" . date('Ymd_His') . ".csv";
        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = array('Tanggal', 'Sekolah Sasaran', 'Menu Makanan', 'Porsi Terkirim', 'Berat Sisa (Kg)', 'Porsi Sisa', 'Persentase Waste (%)', 'Kategori', 'Penyebab');

        $callback = function() use($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($data as $item) {
                fputcsv($file, array(
                    $item->tanggal_distribusi,
                    $item->nama_sekolah,
                    $item->nama_menu,
                    $item->total_porsi_dikirim,
                    $item->berat_sisa_makanan,
                    $item->jumlah_sisa_porsi,
                    $item->persentase_waste,
                    $item->kategori_waste,
                    $item->penyebab_waste
                ));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // F. EXPORT LAPORAN PDF (PRINT DESIGN VIEW)
    public function exportPdf(Request $request)
    {
        $schoolId = $request->input('school_id');
        $menuId = $request->input('menu_id');
        $dateStart = $request->input('date_start');
        $dateEnd = $request->input('date_end');

        $query = DB::table('food_wastes')
            ->join('distributions', 'food_wastes.distribution_id', '=', 'distributions.id')
            ->join('schools', 'distributions.school_id', '=', 'schools.id')
            ->join('menus', 'distributions.menu_id', '=', 'menus.id')
            ->select('food_wastes.*', 'distributions.tanggal_distribusi', 'distributions.total_porsi_dikirim', 'schools.name as nama_sekolah', 'menus.menu_name as nama_menu');

        if ($schoolId) $query->where('distributions.school_id', $schoolId);
        if ($menuId) $query->where('distributions.menu_id', $menuId);
        if ($dateStart && $dateEnd) $query->whereBetween('distributions.tanggal_distribusi', [$dateStart, $dateEnd]);

        $laporanData = $query->orderBy('distributions.tanggal_distribusi', 'desc')->get();

        return view('laporan.print_pdf', compact('laporanData', 'dateStart', 'dateEnd'));
    }
}