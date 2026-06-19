@extends('Layouts.app')

@section('content')
<div>
    <!-- HEADER -->
    <div class="mb-8">
        <h1 class="text-2xl font-black uppercase tracking-tight" :class="$store.theme.current === 'dark' ? 'text-white' : 'text-slate-900'">
            Laporan Historis Distribusi & Food Waste
        </h1>
        <p class="text-xs font-mono" :class="$store.theme.current === 'dark' ? 'text-slate-400' : 'text-slate-500'">
            Hasil integrasi audit kargo pengiriman gizi harian dan sisa konsumsi sekolah sasaran
        </p>
    </div>

    <!-- FILTER PANEL -->
    <div class="p-6 rounded-2xl shadow-xl border mb-8 font-mono text-xs"
         :class="$store.theme.current === 'dark' ? 'bg-slate-900 border-slate-800 text-white' : 'bg-white border-slate-200 text-slate-800'">
        <h3 class="text-emerald-400 font-bold mb-4">🔍 Pilihan Filter Sekolah Sasaran & Rentang Tanggal</h3>
        
        <form action="{{ route('laporan.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div>
                <label class="block text-slate-400 mb-1 font-bold">Sekolah Sasaran</label>
                <select name="school_id" class="w-full bg-slate-950 border border-slate-800 text-white rounded-xl px-3 py-2.5 focus:outline-none focus:border-emerald-500 cursor-pointer">
                    <option value="">-- Semua Sekolah Sasaran --</option>
                    @foreach($schools as $sch)
                        <option value="{{ $sch->id }}" {{ $schoolId == $sch->id ? 'selected' : '' }}>{{ $sch->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-slate-400 mb-1 font-bold">Variasi Menu</label>
                <select name="menu_id" class="w-full bg-slate-950 border border-slate-800 text-white rounded-xl px-3 py-2.5 focus:outline-none focus:border-emerald-500 cursor-pointer">
                    <option value="">-- Semua Menu Gizi --</option>
                    @foreach($menus as $m)
                        <option value="{{ $m->id }}" {{ $menuId == $m->id ? 'selected' : '' }}>{{ $m->menu_name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-slate-400 mb-1 font-bold">Rentang Tanggal</label>
                <div class="flex items-center gap-1">
                    <input type="date" name="date_start" value="{{ $dateStart }}" class="w-1/2 bg-slate-950 border border-slate-800 text-white rounded-xl px-2.5 py-2 focus:outline-none">
                    <span class="text-slate-500 text-[10px]">s/d</span>
                    <input type="date" name="date_end" value="{{ $dateEnd }}" class="w-1/2 bg-slate-950 border border-slate-800 text-white rounded-xl px-2.5 py-2 focus:outline-none">
                </div>
            </div>

            <!-- Action buttons -->
            <div class="flex gap-2 w-full">
                <button type="submit" class="flex-1 bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-2.5 rounded-xl transition cursor-pointer text-center uppercase tracking-wider">
                    FILTER DATA
                </button>
                @if($schoolId || $menuId || $dateStart || $dateEnd)
                    <a href="{{ route('laporan.index') }}" class="bg-rose-950 border border-rose-900 text-rose-400 font-bold px-3 py-2.5 rounded-xl transition flex items-center justify-center">
                        RESET
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- EXPORT ACTIONS & DOWNLOAD -->
    <div class="mb-6 flex justify-end gap-2 text-xs font-mono">
        <a href="{{ route('laporan.export-excel', ['school_id' => $schoolId, 'menu_id' => $menuId, 'date_start' => $dateStart, 'date_end' => $dateEnd]) }}" 
           class="bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700/50 px-4 py-2.5 rounded-xl flex items-center gap-2 transition">
            <i data-lucide="download" class="h-4 w-4"></i> EXCEL / CSV
        </a>
        <a href="{{ route('laporan.export-pdf', ['school_id' => $schoolId, 'menu_id' => $menuId, 'date_start' => $dateStart, 'date_end' => $dateEnd]) }}" 
           target="_blank" 
           class="bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700/50 px-4 py-2.5 rounded-xl flex items-center gap-2 transition">
            <i data-lucide="printer" class="h-4 w-4"></i> PDF FORMAT
        </a>
    </div>

    <!-- CHART GRAPHIC TRENDS -->
    <div class="p-6 rounded-2xl shadow-xl border mb-8"
         :class="$store.theme.current === 'dark' ? 'bg-slate-900 border-slate-800' : 'bg-white border-slate-200'">
        <h3 class="text-xs font-mono font-bold text-slate-400 uppercase tracking-widest mb-4">📊 Tren Indeks Sisa Makanan (%) Berdasarkan Filter Terpilih</h3>
        <div class="h-64">
            <canvas id="canvasLaporanWaste"></canvas>
        </div>
    </div>

    <!-- MAIN TABLE DUMP -->
    <div class="rounded-2xl border overflow-hidden shadow-xl"
         :class="$store.theme.current === 'dark' ? 'bg-slate-900 border-slate-800/80' : 'bg-white border-slate-200'">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b font-mono uppercase tracking-wider text-[10px]"
                        :class="$store.theme.current === 'dark' ? 'bg-slate-950 border-slate-800 text-slate-400' : 'bg-slate-100 border-slate-200 text-slate-500'">
                        <th class="p-4 text-center">No</th>
                        <th class="p-4">Sekolah Sasaran</th>
                        <th class="p-4">Menu Makanan</th>
                        <th class="p-4">Tanggal Distribusi</th>
                        <th class="p-4 text-center">Porsi Terkirim</th>
                        <th class="p-4 text-right">Berat Sisa (Kg)</th>
                        <th class="p-4 text-center">Sisa Setara</th>
                        <th class="p-4 text-center">Indeks Waste (%)</th>
                        <th class="p-4 text-center">Status Porsi</th>
                    </tr>
                </thead>
                <tbody class="divide-y" :class="$store.theme.current === 'dark' ? 'divide-slate-800' : 'divide-slate-100'">
                    @forelse($laporanData as $index => $row)
                        <tr class="hover:bg-slate-500/5 transition">
                            <td class="p-4 text-center text-slate-500 font-mono">{{ $index + 1 }}</td>
                            <td class="p-4 font-bold text-white">{{ $row->nama_sekolah }}</td>
                            <td class="p-4 text-slate-300 font-mono">{{ $row->nama_menu }}</td>
                            <td class="p-4 font-mono text-slate-400">{{ date('d M Y', strtotime($row->tanggal_distribusi)) }}</td>
                            <td class="p-4 text-center font-mono font-bold text-blue-400">{{ $row->total_porsi_dikirim }} Pcs</td>
                            <td class="p-4 text-right font-mono font-bold text-slate-300">{{ $row->berat_sisa_makanan }} Kg</td>
                            <td class="p-4 text-center font-mono font-bold text-slate-300">{{ $row->jumlah_sisa_porsi }} Pcs</td>
                            <td class="p-4 text-center">
                                <span class="px-2 py-0.5 rounded font-bold font-mono" 
                                      :class="$row->persentase_waste > 15 ? 'bg-rose-500/10 text-rose-400 border border-rose-500/20' : 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20'">
                                    {{ $row->persentase_waste }}%
                                </span>
                            </td>
                            <td class="p-4 text-center">
                                @if($row->kategori_waste == 'Tinggi')
                                    <span class="text-[9px] font-bold font-mono bg-rose-500/10 text-rose-400 border border-rose-500/20 px-2 py-1 rounded">PANGKAS KUOTA</span>
                                @else
                                    <span class="text-[9px] font-bold font-mono bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 px-2 py-1 rounded">OPTIMAL</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="p-8 text-center text-slate-500 font-mono">📭 Tidak ada data laporan sisa makanan historis yang cocok dengan filter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- INITIALIZE CHART -->
    <script>
        document.addEventListener('alpine:init', () => {
            const ctx = document.getElementById('canvasLaporanWaste').getContext('2d');
            
            const labels = {!! json_encode($chartLabels) !!};
            const dataValues = {!! json_encode($chartData) !!};

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels.length > 0 ? labels : ['Mulai'],
                    datasets: [{
                        label: 'Indeks Food Waste (%)',
                        data: dataValues.length > 0 ? dataValues : [0],
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.05)',
                        borderWidth: 3,
                        pointBackgroundColor: '#f59e0b',
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