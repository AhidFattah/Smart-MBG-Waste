@extends('layouts.app')

@section('content')
<div class="px-4 py-2 max-w-3xl mx-auto">
    
    <div class="bg-white rounded-lg shadow-md p-6 mb-6 border-l-4 border-blue-500">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">Input Log Evaluasi Sisa Makanan</h2>
        <div class="grid grid-cols-2 gap-4 text-sm text-gray-600">
            <div>
                <p class="font-semibold text-gray-400">Nama Sekolah</p>
                <p class="text-base text-gray-800 font-bold">{{ $distribution->school->name }}</p>
            </div>
            <div>
                <p class="font-semibold text-gray-400">Tanggal Distribusi</p>
                <p class="text-base text-gray-800 font-bold">{{ \Carbon\Carbon::parse($distribution->distribution_date)->translatedFormat('d F Y') }}</p>
            </div>
            <div class="col-span-2 border-t border-gray-100 pt-2 mt-2">
                <p class="font-semibold text-gray-400">Menu Konsumsi Hari Ini</p>
                <p class="text-base text-blue-600 font-bold">{{ $distribution->menu->menu_name }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        
        <div class="mb-6 bg-slate-100 rounded-lg p-4 flex justify-between items-center">
            <span class="text-sm font-semibold text-gray-600">Total Manifes Distribusi Dapur:</span>
            <span class="text-xl font-black text-slate-800"><span id="target_qty">{{ $distribution->qty_sent }}</span> Porsi</span>
        </div>

        @if ($errors->has('integrity'))
            <div class="mb-4 bg-orange-50 border-l-4 border-orange-500 text-orange-700 p-4 text-sm rounded shadow-sm">
                {{ $errors->first('integrity') }}
            </div>
        @endif

        <form action="{{ route('sekolah.waste.store', $distribution->id) }}" method="POST" oninput="calculateLiveTotal()" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-green-50 p-4 rounded-lg border border-green-100">
                    <label class="block text-sm font-bold text-green-800 mb-2">1. Kategori HABIS</label>
                    <span class="text-xs text-green-600 block mb-3">(Sisa makanan per porsi 0% - 10%)</span>
                    <input type="number" name="qty_habis" id="qty_habis" value="{{ old('qty_habis', 0) }}" min="0" required
                        class="w-full px-3 py-2 bg-white border border-green-300 rounded focus:ring-2 focus:ring-green-500 text-gray-800 font-bold text-lg">
                </div>

                <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-100">
                    <label class="block text-sm font-bold text-yellow-800 mb-2">2. SISA SEBAGIAN</label>
                    <span class="text-xs text-yellow-600 block mb-3">(Sisa makanan per porsi 11% - 50%)</span>
                    <input type="number" name="qty_sebagian" id="qty_sebagian" value="{{ old('qty_sebagian', 0) }}" min="0" required
                        class="w-full px-3 py-2 bg-white border border-yellow-300 rounded focus:ring-2 focus:ring-yellow-500 text-gray-800 font-bold text-lg">
                </div>

                <div class="bg-red-50 p-4 rounded-lg border border-red-100">
                    <label class="block text-sm font-bold text-red-800 mb-2">3. TIDAK DIHABISKAN</label>
                    <span class="text-xs text-red-600 block mb-3">(Sisa makanan per porsi 51% - 100%)</span>
                    <input type="number" name="qty_tidak_habis" id="qty_tidak_habis" value="{{ old('qty_tidak_habis', 0) }}" min="0" required
                        class="w-full px-3 py-2 bg-white border border-red-300 rounded focus:ring-2 focus:ring-red-500 text-gray-800 font-bold text-lg">
                </div>
            </div>

            <div class="border-t border-gray-100 pt-4 flex justify-between items-center">
                <div>
                    <p class="text-sm font-medium text-gray-500">Total Akumulasi Input Saat Ini:</p>
                    <p class="text-2xl font-black text-gray-800"><span id="live_total">0</span> / {{ $distribution->qty_sent }} Porsi</p>
                </div>
                <div id="status_indicator" class="text-sm font-bold px-3 py-1 rounded shadow-inner">
                    Memuat kalkulasi...
                </div>
            </div>

            <button type="submit" id="btn_submit"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition duration-150 shadow-md">
                Proses & Jalankan Komputasi DSS
            </button>
        </form>
    </div>
</div>

<script>
    function calculateLiveTotal() {
        const target = parseInt(document.getElementById('target_qty').innerText) || 0;
        const habis = parseInt(document.getElementById('qty_habis').value) || 0;
        const sebagian = parseInt(document.getElementById('qty_sebagian').value) || 0;
        const tidakHabis = parseInt(document.getElementById('qty_tidak_habis').value) || 0;

        const total = habis + sebagian + tidakHabis;
        document.getElementById('live_total').innerText = total;

        const indicator = document.getElementById('status_indicator');
        const btnSubmit = document.getElementById('btn_submit');

        if (total === target) {
            indicator.innerText = "✓ SINKRON (Data Valid)";
            indicator.className = "text-sm font-bold px-3 py-1 rounded bg-green-100 text-green-700";
            btnSubmit.disabled = false;
            btnSubmit.className = "w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition duration-150 shadow-md cursor-pointer";
        } else {
            indicator.innerText = "⚠ BELUM SINKRON";
            indicator.className = "text-sm font-bold px-3 py-1 rounded bg-red-100 text-red-700";
            btnSubmit.disabled = true;
            btnSubmit.className = "w-full bg-gray-400 text-gray-200 font-bold py-3 px-4 rounded-lg cursor-not-allowed opacity-60";
        }
    }

    // Jalankan kalkulasi pertama kali saat halaman dimuat
    document.addEventListener("DOMContentLoaded", calculateLiveTotal);
</script>
@endsection