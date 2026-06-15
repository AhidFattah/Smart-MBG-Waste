<?php

namespace App\Services;

use App\Models\Distribution;
use App\Models\WasteLog;
use Illuminate\Support\Facades\DB;

class DecisionSupportService
{
    public function calculateAndAnalyze(Distribution $distribution, array $data): WasteLog
    {
        $qty_sent = $distribution->qty_sent;
        $habis = $data['qty_habis'];
        $sebagian = $data['qty_sebagian'];
        $tidak_habis = $data['qty_tidak_habis'];

        // Indeks Food Waste (Bobot Sebagian = 0.3, Tidak Habis = 1.0)
        $weighted_waste = ($sebagian * 0.3) + ($tidak_habis * 1.0);
        $fw_index = round(($weighted_waste / $qty_sent) * 100, 2);

        // Aturan Rekomendasi
        if ($fw_index < 5.0) {
            $status = "Distribusi Optimal. Pertahankan volume produksi.";
            $base_next_qty = $qty_sent;
        } elseif ($fw_index >= 5.0 && $fw_index <= 10.0) {
            $status = "Evaluasi Menu. Diperlukan peninjauan kombinasi lauk.";
            $base_next_qty = $qty_sent;
        } else {
            $status = "Reduksi Produksi. Tingkat pemborosan tinggi.";
            $base_next_qty = $qty_sent * 0.90; 
        }

        // Prediksi Berbasis Data Historis (3-Days Moving Average)
        $historical_average_index = DB::table('waste_logs')
            ->join('distributions', 'waste_logs.distribution_id', '=', 'distributions.id')
            ->where('distributions.school_id', $distribution->school_id)
            ->where('distributions.menu_id', $distribution->menu_id)
            ->latest('waste_logs.created_at')
            ->take(3)
            ->avg('food_waste_index');

        if ($historical_average_index && $historical_average_index > 10.0) {
            $recommended_next_qty = (int) floor($base_next_qty * 0.95);
        } else {
            $recommended_next_qty = (int) round($base_next_qty);
        }

        if ($recommended_next_qty < 10) {
            $recommended_next_qty = $qty_sent; 
        }

        return WasteLog::create([
            'distribution_id'       => $distribution->id,
            'qty_habis'             => $habis,
            'qty_sebagian'          => $sebagian,
            'qty_tidak_habis'       => $tidak_habis,
            'food_waste_index'      => $fw_index,
            'recommendation_status' => $status,
            'recommended_next_qty'  => $recommended_next_qty
        ]);
    }
}