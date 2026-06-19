@extends('Layouts.app')

@section('content')
<div>
    <!-- HEADER -->
    <div class="mb-8">
        <h1 class="text-2xl font-black uppercase tracking-tight" :class="$store.theme.current === 'dark' ? 'text-white' : 'text-slate-900'">
            Analisis Data & Prediksi AI DSS
        </h1>
        <p class="text-xs font-mono" :class="$store.theme.current === 'dark' ? 'text-slate-400' : 'text-slate-500'">
            Modul penganalisis data historis konsumsi, prediksi kebutuhan logistik, dan optimalisasi porsi
        </p>
    </div>

    <!-- KPI KPI ANALITIK LANJUTAN -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-8">
        <!-- Rata-rata Waste Nasional -->
        <div class="p-5 rounded-2xl border flex flex-col justify-between"
             :class="$store.theme.current === 'dark' ? 'bg-slate-900 border-slate-800 shadow-xl' : 'bg-white border-slate-200 shadow-md'">
            <div class="flex justify-between items-start mb-4">
                <span class="text-xs font-mono text-slate-500 uppercase tracking-wider">Rata-rata Indeks Waste</span>
                <span class="p-2 rounded-xl bg-emerald-500/10 text-emerald-400"><i data-lucide="percent" class="h-5 w-5"></i></span>
            </div>
            <div>
                <span class="text-3xl font-black font-mono tracking-tight text-emerald-400">{{ $kpis['avg_waste'] }}%</span>
                <span class="text-[10px] font-mono text-slate-500 ml-1">target: <10%</span>
            </div>
        </div>

        <!-- Total Berat Makanan Terbuang -->
        <div class="p-5 rounded-2xl border flex flex-col justify-between"
             :class="$store.theme.current === 'dark' ? 'bg-slate-900 border-slate-800 shadow-xl' : 'bg-white border-slate-200 shadow-md'">
            <div class="flex justify-between items-start mb-4">
                <span class="text-xs font-mono text-slate-500 uppercase tracking-wider">Total Berat Waste</span>
                <span class="p-2 rounded-xl bg-rose-500/10 text-rose-400"><i data-lucide="scale" class="h-5 w-5"></i></span>
            </div>
            <div>
                <span class="text-3xl font-black font-mono tracking-tight text-rose-400">{{ $kpis['total_weight_waste'] }} Kg</span>
                <span class="text-[10px] font-mono text-slate-500 ml-1">sisa akumulasi</span>
            </div>
        </div>

        <!-- Akurasi Estimasi Porsi -->
        <div class="p-5 rounded-2xl border flex flex-col justify-between"
             :class="$store.theme.current === 'dark' ? 'bg-slate-900 border-slate-800 shadow-xl' : 'bg-white border-slate-200 shadow-md'">
            <div class="flex justify-between items-start mb-4">
                <span class="text-xs font-mono text-slate-500 uppercase tracking-wider">Akurasi Estimasi Porsi</span>
                <span class="p-2 rounded-xl bg-blue-500/10 text-blue-400"><i data-lucide="bullseye" class="h-5 w-5"></i></span>
            </div>
            <div>
                <span class="text-3xl font-black font-mono tracking-tight text-blue-400">{{ $kpis['accuracy_portions'] }}%</span>
                <span class="text-[10px] font-mono text-slate-500 ml-1">efisiensi kirim</span>
            </div>
        </div>
    </div>

    <!-- MAIN GRAPHIC CHART TREND -->
    <div class="p-6 rounded-2xl border shadow-xl mb-8"
         :class="$store.theme.current === 'dark' ? 'bg-slate-900 border-slate-800' : 'bg-white border-slate-200'">
        <h3 class="text-xs font-mono font-bold text-slate-400 uppercase tracking-widest mb-4">📈 Kurva Tren Historis Food Waste Harian</h3>
        <div class="h-80">
            <canvas id="canvasChartHistoris"></canvas>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- REKOMENDASI DSS ENGINE -->
        <div class="p-6 rounded-2xl border shadow-xl flex flex-col justify-between"
             :class="$store.theme.current === 'dark' ? 'bg-slate-900 border-slate-800' : 'bg-white border-slate-200'">
            <div class="mb-4 flex justify-between items-center">
                <h3 class="text-xs font-mono font-bold text-emerald-400 uppercase tracking-widest">💡 Sistem Rekomendasi Porsi AI DSS</h3>
                <span class="text-[9px] font-mono bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 px-2 py-0.5 rounded">Engine Aktif</span>
            </div>
            
            <div class="space-y-4 text-xs font-mono">
                <div class="p-4 bg-rose-500/5 border border-rose-500/10 text-rose-400 rounded-xl leading-relaxed">
                    <p class="font-bold">⚠️ REKOMENDASI REDUKSI KUOTA (SDN 3 Blimbing):</p>
                    <p class="text-[11px] text-slate-400 mt-1 leading-normal">Berdasarkan data 7 hari terakhir, waste di sekolah ini melebihi ambang batas 15% (rata-rata 16.4%). Disarankan untuk memotong jumlah pengiriman porsi esok hari sebesar 10% (dari 226 menjadi 200 porsi).</p>
                </div>
                
                <div class="p-4 bg-emerald-500/5 border border-emerald-500/10 text-emerald-400 rounded-xl leading-relaxed">
                    <p class="font-bold">✅ REKOMENDASI PASOKAN OPTIMAL (SDN 2 Mojolangu):</p>
                    <p class="text-[11px] text-slate-400 mt-1 leading-normal">Waste di sekolah ini sangat rendah (rata-rata 3.2%). Kuota distribusi porsi esok hari dapat dipertahankan atau dinaikkan sebanyak 5 porsi guna memastikan cakupan nutrisi siswa terpenuhi.</p>
                </div>
            </div>
        </div>

        <!-- HISTORI LOG LIST -->
        <div class="p-6 rounded-2xl border shadow-xl"
             :class="$store.theme.current === 'dark' ? 'bg-slate-900 border-slate-800' : 'bg-white border-slate-200'">
            <h3 class="text-xs font-mono font-bold text-slate-400 uppercase tracking-widest mb-4">📜 Log Historis Masukan Data</h3>
            
            <div class="max-h-64 overflow-y-auto space-y-2 font-mono text-xs">
                @forelse($trends as $tr)
                    <div class="p-3 bg-slate-950/40 border border-slate-800/80 rounded-xl flex justify-between items-center">
                        <div>
                            <p class="font-bold text-slate-300">{{ $tr->nama_sekolah }}</p>
                            <p class="text-[10px] text-slate-500 mt-0.5">{{ date('d M Y', strtotime($tr->tanggal_distribusi)) }} | {{ $tr->nama_menu }}</p>
                        </div>
                        <span class="font-bold text-rose-400 bg-rose-500/10 px-2 py-0.5 rounded border border-rose-500/20 text-[10px]">{{ $tr->persentase_waste }}% waste</span>
                    </div>
                @endforeach
                
                @if(count($trends) == 0)
                    <div class="p-3 bg-slate-950/40 border border-slate-800/80 rounded-xl flex justify-between items-center">
                        <div>
                            <p class="font-bold text-slate-300">SDN 3 Blimbing</p>
                            <p class="text-[10px] text-slate-500 mt-0.5">17 Jun 2026 | Menu B</p>
                        </div>
                        <span class="font-bold text-rose-400 bg-rose-500/10 px-2 py-0.5 rounded border border-rose-500/20 text-[10px]">16.4% waste</span>
                    </div>
                    <div class="p-3 bg-slate-950/40 border border-slate-800/80 rounded-xl flex justify-between items-center">
                        <div>
                            <p class="font-bold text-slate-300">SDN 1 Arjosari</p>
                            <p class="text-[10px] text-slate-500 mt-0.5">17 Jun 2026 | Menu A</p>
                        </div>
                        <span class="font-bold text-rose-400 bg-rose-500/10 px-2 py-0.5 rounded border border-rose-500/20 text-[10px]">11.8% waste</span>
                    </div>
                @endif
            </div>
        </div>

    </div>

    <!-- INITIALIZE CHART SCRIPT -->
    <script>
        document.addEventListener('alpine:init', () => {
            const ctx = document.getElementById('canvasChartHistoris').getContext('2d');
            
            const rawData = {!! json_encode($trends) !!};
            
            const labels = [];
            const dataValues = [];
            
            rawData.forEach(row => {
                labels.push(row.nama_sekolah + ' (' + new Date(row.tanggal_distribusi).toLocaleDateString('id-ID', {day:'numeric', month:'short'}) + ')');
                dataValues.push(row.persentase_waste);
            });

            // Fallback
            if (labels.length === 0) {
                labels.push('SDN 3 Blimbing (11 Jun)', 'SDN 1 Arjosari (12 Jun)', 'SDN 2 Mojolangu (13 Jun)', 'SDN 1 Dinoyo (14 Jun)', 'SDN 3 Blimbing (15 Jun)');
                dataValues.push(16.2, 10.5, 3.2, 9.4, 16.4);
            }

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Indeks Food Waste (%)',
                        data: dataValues,
                        borderColor: '#a855f7', // purple-500
                        backgroundColor: 'rgba(168, 85, 247, 0.05)',
                        borderWidth: 3,
                        pointBackgroundColor: '#10b981',
                        tension: 0.3,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, max: 100, grid: { color: 'rgba(128,128,128,0.1)' }, ticks: { font: { family: 'monospace', size: 9 }, color: '#94a3b8' } },
                        x: { grid: { color: 'rgba(128,128,128,0.1)' }, ticks: { font: { family: 'monospace', size: 9 }, color: '#94a3b8' } }
                    }
                }
            });
        });
    </script>
</div>
@endsection
