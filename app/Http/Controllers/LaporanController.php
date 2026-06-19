<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        // 1. Ambil Parameter Filter dari Request
        $tanggal = $request->input('tanggal');
        $bulan = $request->input('bulan');
        $school_id = $request->input('school_id');
        $menu_id = $request->input('menu_id');

        // 2. Ambil Data Master untuk Dropdown Filter
        $schools = DB::table('schools')->get();
        $menus = DB::table('menus')->get();

        // 3. Query Laporan Inventaris
        $inventories = DB::table('inventories')->orderBy('nama_bahan', 'asc')->get();

        // 4. Query Laporan Utama (Gabungan Distribusi Makanan & Food Waste)
        $query = DB::table('distributions')
            ->join('schools', 'distributions.school_id', '=', 'schools.id')
            ->join('menus', 'distributions.menu_id', '=', 'menus.id')
            ->leftJoin('food_waste_logs', 'distributions.id', '=', 'food_waste_logs.distribution_id')
            ->select(
                'distributions.*',
                'schools.name as nama_sekolah',
                'menus.menu_name',
                'food_waste_logs.indeks_waste',
                'food_waste_logs.qty_habis',
                'food_waste_logs.qty_sebagian',
                'food_waste_logs.qty_tidak_habis',
                'food_waste_logs.rekomendasi_dss',
                'food_waste_logs.faktor_penyebab'
            );

        // Terapkan Filter jika dipilih oleh user
        if ($tanggal) {
            $query->whereDate('distributions.tanggal_distribusi', $tanggal);
        }
        if ($bulan) {
            $query->whereMonth('distributions.tanggal_distribusi', '=', date('m', strtotime($bulan)))
                  ->whereYear('distributions.tanggal_distribusi', '=', date('Y', strtotime($bulan)));
        }
        if ($school_id) {
            $query->where('distributions.school_id', $school_id);
        }
        if ($menu_id) {
            $query->where('distributions.menu_id', $menu_id);
        }

        $laporanData = $query->orderBy('distributions.tanggal_distribusi', 'desc')->get();

        // 5. Persiapan Data Eksklusif untuk Chart.js (Grafik Tren Analisis)
        $chartDates = [];
        $chartWaste = [];
        $chartPorsi = [];

        foreach ($laporanData->reverse() as $data) {
            $chartDates[] = date('d M', strtotime($data->tanggal_distribusi)) . ' (' . substr($data->nama_sekolah, 0, 6) . ')';
            $chartWaste[] = $data->indeks_waste ?? 0;
            $chartPorsi[] = $data->total_porsi_dikirim;
        }

        return view('laporan.index', compact(
            'laporanData', 'inventories', 'schools', 'menus',
            'tanggal', 'bulan', 'school_id', 'menu_id',
            'chartDates', 'chartWaste', 'chartPorsi'
        ));
    }
}