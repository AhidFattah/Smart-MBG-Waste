<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Distribution;
use App\Services\DecisionSupportService;
use Illuminate\Support\Facades\DB;
use Exception;

class FoodWasteController extends Controller
{
    protected $dssService;

    public function __construct(DecisionSupportService $dssService)
    {
        $this->dssService = $dssService;
    }

    public function createWasteLog($distribution_id)
    {
        $distribution = Distribution::with(['school', 'menu'])->findOrFail($distribution_id);
        
        if ($distribution->status === 'completed') {
            return redirect()->route('dashboard')->with('error', 'Data sisa makanan untuk distribusi ini telah diinput sebelumnya.');
        }

        return view('sekolah.waste_input', compact('distribution'));
    }

    public function storeWasteLog(Request $request, $distribution_id)
    {
        $request->validate([
            'qty_habis'       => 'required|integer|min:0',
            'qty_sebagian'    => 'required|integer|min:0',
            'qty_tidak_habis' => 'required|integer|min:0',
        ]);

        $distribution = Distribution::findOrFail($distribution_id);
        $total_input = $request->qty_habis + $request->qty_sebagian + $request->qty_tidak_habis;

        if ($total_input !== $distribution->qty_sent) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['integrity' => "Jumlah kalkulasi porsi lapangan ({$total_input}) tidak sesuai dengan porsi terkirim ({$distribution->qty_sent})."]);
        }

        DB::beginTransaction();
        try {
            $this->dssService->calculateAndAnalyze($distribution, $request->only(['qty_habis', 'qty_sebagian', 'qty_tidak_habis']));
            $distribution->update(['status' => 'completed']);
            
            DB::commit();
            return redirect()->route('dashboard')->with('success', 'Data sisa makanan berhasil dianalisis.');
            
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->withErrors(['system_error' => 'Gagal memproses data: ' . $e->getMessage()]);
        }
    }
}