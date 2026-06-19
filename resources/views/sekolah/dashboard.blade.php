@extends('Layouts.app')

@section('content')
<div x-data="dashboardState">

    <!-- DYNAMIC FILTERS & HEADER -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-black uppercase tracking-tight" :class="$store.theme.current === 'dark' ? 'text-white' : 'text-slate-900'">
                {{ session('locale') == 'en' ? 'MBG Command Center' : 'Pusat Kendali Real-Time MBG' }}
            </h1>
            <p class="text-xs font-mono" :class="$store.theme.current === 'dark' ? 'text-slate-400' : 'text-slate-500'">
                {{ session('locale') == 'en' ? 'Interactive monitoring dashboard for Healthy Nutrient Distributions' : 'Status pemantauan pelaksanaan program makanan bergizi gratis & audit sisa makanan' }}
            </p>
        </div>
        
        <div class="flex flex-wrap items-center gap-2">
            <!-- Toggle Widget Layout -->
            <button @click="showCustomizer = !showCustomizer" 
                    class="bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold font-mono px-3.5 py-2.5 rounded-xl border border-slate-700/50 flex items-center gap-2 cursor-pointer transition">
                <i data-lucide="layout" class="h-4 w-4"></i>
                <span x-text="showCustomizer ? 'TUTUP PENGATUR' : 'ATUR TATA LETAK'"></span>
            </button>

            <!-- Periode Filter Form -->
            <form action="{{ route('dashboard') }}" method="GET" class="flex items-center gap-2">
                <select name="periode" onchange="this.form.submit()" 
                        class="bg-slate-900 border border-slate-800 text-xs font-mono text-slate-300 px-3.5 py-2.5 rounded-xl focus:outline-none focus:border-emerald-500 cursor-pointer">
                    <option value="hari_ini" {{ $periode == 'hari_ini' ? 'selected' : '' }}>Hari Ini (Real-Time)</option>
                    <option value="minggu_ini" {{ $periode == 'minggu_ini' ? 'selected' : '' }}>7 Hari Terakhir</option>
                    <option value="bulan_ini" {{ $periode == 'bulan_ini' ? 'selected' : '' }}>Bulan Ini</option>
                    <option value="tahun_ini" {{ $periode == 'tahun_ini' ? 'selected' : '' }}>Tahun Ini</option>
                    <option value="custom" {{ $periode == 'custom' ? 'selected' : '' }}>Rentang Kustom</option>
                </select>

                @if($periode == 'custom')
                    <input type="date" name="start_date" value="{{ $startDate }}" class="bg-slate-900 border border-slate-800 text-xs font-mono text-slate-300 px-3.5 py-2 rounded-xl">
                    <span class="text-xs text-slate-500 font-mono">s/d</span>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="bg-slate-900 border border-slate-800 text-xs font-mono text-slate-300 px-3.5 py-2 rounded-xl">
                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-500 text-white font-mono text-xs px-3 py-2 rounded-xl cursor-pointer">OK</button>
                @endif
            </form>
        </div>
    </div>

    <!-- CUSTOMIZER DRAWER PANEL -->
    <div x-show="showCustomizer" x-transition class="mb-6 p-5 rounded-2xl border"
         :class="$store.theme.current === 'dark' ? 'bg-slate-900 border-slate-800' : 'bg-white border-slate-200 shadow-lg'">
        <h3 class="text-xs font-mono font-bold text-emerald-400 uppercase tracking-widest mb-3">🛠️ Susun Urutan Panel Utama Dashboard</h3>
        <p class="text-[10px] text-slate-500 mb-4">Seret & taruh item di bawah ini untuk mengatur urutan tampilan widget.</p>
        
        <div class="flex flex-wrap gap-2">
            <template x-for="(w, idx) in widgets" :key="w">
                <div draggable="true"
                     @dragstart="draggedIndex = idx"
                     @dragover.prevent
                     @drop="
                        const temp = widgets[draggedIndex];
                        widgets.splice(draggedIndex, 1);
                        widgets.splice(idx, 0, temp);
                        saveWidgetOrder();
                     "
                     class="px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-[11px] font-mono font-bold text-slate-300 flex items-center gap-2 cursor-move hover:border-emerald-500 transition">
                    <i data-lucide="grip-vertical" class="h-4 w-4 text-slate-500"></i>
                    <span x-text="w.toUpperCase().replace('_', ' ')"></span>
                </div>
            </template>
        </div>
    </div>

    <!-- WIDGET GRID (FLEXIBLE DRAGGED ORDER STATE) -->
    <div class="space-y-6">
        
        <!-- RENDER ORDER LOOP -->
        <template x-for="widget in widgets" :key="widget">
            <div class="w-full">
                
                <!-- KPI CARDS WIDGET -->
                <div x-show="widget === 'kpi'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                    <!-- Total Sekolah -->
                    <div class="p-5 rounded-2xl border flex flex-col justify-between hover:-translate-y-1 transition duration-300"
                         :class="$store.theme.current === 'dark' ? 'bg-slate-900 border-slate-800 shadow-xl' : 'bg-white border-slate-200 shadow-md'">
                        <div class="flex justify-between items-start mb-4">
                            <span class="text-xs font-mono text-slate-500 uppercase tracking-wider">Sekolah Terdaftar</span>
                            <span class="p-2 rounded-xl bg-emerald-500/10 text-emerald-400"><i data-lucide="graduation-cap" class="h-5 w-5"></i></span>
                        </div>
                        <div>
                            <span class="text-3xl font-black font-mono tracking-tight">{{ $stats['total_sekolah'] }}</span>
                            <span class="text-[10px] font-mono text-slate-500 ml-1">Lembaga</span>
                        </div>
                    </div>

                    <!-- Porsi Terkirim -->
                    <div class="p-5 rounded-2xl border flex flex-col justify-between hover:-translate-y-1 transition duration-300"
                         :class="$store.theme.current === 'dark' ? 'bg-slate-900 border-slate-800 shadow-xl' : 'bg-white border-slate-200 shadow-md'">
                        <div class="flex justify-between items-start mb-4">
                            <span class="text-xs font-mono text-slate-500 uppercase tracking-wider">Kuota Distribusi</span>
                            <span class="p-2 rounded-xl bg-blue-500/10 text-blue-400"><i data-lucide="box" class="h-5 w-5"></i></span>
                        </div>
                        <div>
                            <span class="text-3xl font-black font-mono tracking-tight text-blue-400">{{ number_format($stats['total_porsi_kirim']) }}</span>
                            <span class="text-[10px] font-mono text-slate-500 ml-1">Porsi</span>
                        </div>
                    </div>

                    <!-- Persentase Waste -->
                    <div class="p-5 rounded-2xl border flex flex-col justify-between hover:-translate-y-1 transition duration-300"
                         :class="$store.theme.current === 'dark' ? 'bg-slate-900 border-slate-800 shadow-xl' : 'bg-white border-slate-200 shadow-md'">
                        <div class="flex justify-between items-start mb-4">
                            <span class="text-xs font-mono text-slate-500 uppercase tracking-wider">Persentase Food Waste</span>
                            <span class="p-2 rounded-xl" :class="{{ $stats['waste_percent'] }} > 15 ? 'bg-rose-500/10 text-rose-400' : 'bg-emerald-500/10 text-emerald-400'">
                                <i data-lucide="trash-2" class="h-5 w-5"></i>
                            </span>
                        </div>
                        <div>
                            <span class="text-3xl font-black font-mono tracking-tight" :class="{{ $stats['waste_percent'] }} > 15 ? 'text-rose-400' : 'text-emerald-400'">
                                {{ $stats['waste_percent'] }}%
                            </span>
                            <span class="text-[10px] font-mono text-slate-500 ml-1">dari kuota kirim</span>
                        </div>
                    </div>

                    <!-- Total Gudang -->
                    <div class="p-5 rounded-2xl border flex flex-col justify-between hover:-translate-y-1 transition duration-300"
                         :class="$store.theme.current === 'dark' ? 'bg-slate-900 border-slate-800 shadow-xl' : 'bg-white border-slate-200 shadow-md'">
                        <div class="flex justify-between items-start mb-4">
                            <span class="text-xs font-mono text-slate-500 uppercase tracking-wider">Logistik Gudang</span>
                            <span class="p-2 rounded-xl bg-amber-500/10 text-amber-400"><i data-lucide="archive" class="h-5 w-5"></i></span>
                        </div>
                        <div>
                            <span class="text-3xl font-black font-mono tracking-tight text-amber-400">{{ number_format($stats['total_inventaris']) }}</span>
                            <span class="text-[10px] font-mono text-slate-500 ml-1">Kg / Ltr</span>
                        </div>
                    </div>
                </div>

                <!-- DUAL CHARTS WIDGET -->
                <div x-show="widget === 'waste_trend' || widget === 'dist_volume'" class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
                    
                    <!-- Line Chart Waste Harian -->
                    <div class="p-6 rounded-2xl border shadow-xl"
                         :class="$store.theme.current === 'dark' ? 'bg-slate-900 border-slate-800' : 'bg-white border-slate-200'">
                        <h3 class="text-xs font-mono font-bold text-slate-400 uppercase tracking-widest mb-4">📈 Tren Indeks Food Waste Harian (%)</h3>
                        <div class="h-64 relative">
                            <canvas id="canvasWaste"></canvas>
                        </div>
                    </div>

                    <!-- Bar Chart Volume Distribusi -->
                    <div class="p-6 rounded-2xl border shadow-xl"
                         :class="$store.theme.current === 'dark' ? 'bg-slate-900 border-slate-800' : 'bg-white border-slate-200'">
                        <h3 class="text-xs font-mono font-bold text-slate-400 uppercase tracking-widest mb-4">📊 Volume Pengiriman Porsi Makanan Harian</h3>
                        <div class="h-64 relative">
                            <canvas id="canvasDist"></canvas>
                        </div>
                    </div>
                </div>

                <!-- SUB GRID (HEATMAP & PIE CHART) -->
                <div x-show="widget === 'waste_pie' || widget === 'heatmap'" class="grid grid-cols-1 lg:grid-cols-12 gap-6 mt-6">
                    
                    <!-- Pie Chart Kategori (4 Cols) -->
                    <div class="lg:col-span-4 p-6 rounded-2xl border shadow-xl flex flex-col justify-between"
                         :class="$store.theme.current === 'dark' ? 'bg-slate-900 border-slate-800' : 'bg-white border-slate-200'">
                        <h3 class="text-xs font-mono font-bold text-slate-400 uppercase tracking-widest mb-4">🍩 Kategori Sisa Makanan</h3>
                        <div class="h-48 relative flex justify-center items-center">
                            <canvas id="canvasPie"></canvas>
                        </div>
                        <div class="mt-4 flex justify-around text-[10px] font-mono">
                            <span class="text-emerald-400">Sedikit (0-10%)</span>
                            <span class="text-amber-400">Sedang (11-20%)</span>
                            <span class="text-rose-400">Tinggi (>20%)</span>
                        </div>
                    </div>

                    <!-- SVG HEATMAP REGIONAL (8 Cols) -->
                    <div class="lg:col-span-8 p-6 rounded-2xl border shadow-xl"
                         :class="$store.theme.current === 'dark' ? 'bg-slate-900 border-slate-800' : 'bg-white border-slate-200'">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-xs font-mono font-bold text-slate-400 uppercase tracking-widest">🗺️ Peta Heatmap Food Waste Sekolah Sasaran (Malang)</h3>
                            <span class="text-[9px] font-mono bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 px-2 py-0.5 rounded">Regional Grid</span>
                        </div>
                        <div class="flex flex-col sm:flex-row gap-6 items-center">
                            <!-- SVG Map Grid Simulator -->
                            <div class="w-full sm:w-2/3 bg-slate-950/40 p-4 rounded-xl border border-slate-800/80 flex items-center justify-center relative">
                                <svg viewBox="0 0 400 300" class="w-full h-auto max-h-60 max-w-sm">
                                    <!-- Region Blimbing (Tinggi - Merah) -->
                                    <path d="M50 30 L180 30 L160 120 L40 100 Z" fill="rgba(244, 63, 94, 0.15)" stroke="#f43f5e" stroke-width="2" />
                                    <circle cx="100" cy="70" r="8" fill="#f43f5e" />
                                    <text x="70" y="50" fill="#f43f5e" font-family="monospace" font-size="8" font-weight="bold">SDN 3 Blimbing (16.4%)</text>
                                    
                                    <!-- Region Lowokwaru (Sedikit - Hijau) -->
                                    <path d="M180 30 L350 50 L320 180 L160 120 Z" fill="rgba(16, 185, 129, 0.15)" stroke="#10b981" stroke-width="2" />
                                    <circle cx="250" cy="100" r="8" fill="#10b981" />
                                    <text x="210" y="85" fill="#10b981" font-family="monospace" font-size="8" font-weight="bold">SDN 1 Dinoyo (9.5%)</text>
                                    <circle cx="200" cy="140" r="8" fill="#10b981" />
                                    <text x="215" y="145" fill="#10b981" font-family="monospace" font-size="8" font-weight="bold">SDN 2 Mojolangu (3.2%)</text>
                                    
                                    <!-- Region Kedungkandang / Arjosari (Sedang - Kuning) -->
                                    <path d="M40 100 L160 120 L180 280 L30 250 Z" fill="rgba(245, 158, 11, 0.15)" stroke="#f59e0b" stroke-width="2" />
                                    <circle cx="95" cy="180" r="8" fill="#f59e0b" />
                                    <text x="50" y="165" fill="#f59e0b" font-family="monospace" font-size="8" font-weight="bold">SDN 1 Arjosari (11.8%)</text>
                                </svg>
                            </div>
                            
                            <!-- Legenda Peta -->
                            <div class="w-full sm:w-1/3 text-xs font-mono space-y-4">
                                <div class="p-3 bg-rose-500/10 border border-rose-500/20 text-rose-400 rounded-xl">
                                    <p class="font-bold">🔴 Zona Merah (>15%)</p>
                                    <p class="text-[10px] mt-1 text-slate-400 leading-normal">Sekolah di zona ini membutuhkan penyesuaian porsi segera.</p>
                                </div>
                                <div class="p-3 bg-amber-500/10 border border-amber-500/20 text-amber-400 rounded-xl">
                                    <p class="font-bold">🟡 Zona Kuning (10-15%)</p>
                                    <p class="text-[10px] mt-1 text-slate-400 leading-normal">Zona pemantauan ketat rotasi menu mingguan.</p>
                                </div>
                                <div class="p-3 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-xl">
                                    <p class="font-bold">🟢 Zona Hijau (<10%)</p>
                                    <p class="text-[10px] mt-1 text-slate-400 leading-normal">Zona efisiensi optimal gizi terserap maksimal.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- BOTTOM STATS GRID (RANKINGS & AI INSIGHTS) -->
                <div x-show="widget === 'rankings' || widget === 'ai_insights'" class="grid grid-cols-1 lg:grid-cols-12 gap-6 mt-6">
                    
                    <!-- Rankings (5 Cols) -->
                    <div class="lg:col-span-5 p-6 rounded-2xl border shadow-xl flex flex-col justify-between"
                         :class="$store.theme.current === 'dark' ? 'bg-slate-900 border-slate-800' : 'bg-white border-slate-200'">
                        <h3 class="text-xs font-mono font-bold text-slate-400 uppercase tracking-widest mb-4">🏆 Ranking Efisiensi Konsumsi Sekolah</h3>
                        
                        <div class="space-y-2.5 font-mono text-xs">
                            @foreach($schoolRankings as $index => $rank)
                                <div class="p-3 bg-slate-950/40 border border-slate-800/80 rounded-xl flex justify-between items-center hover:border-emerald-500/40 transition">
                                    <div class="flex items-center gap-2">
                                        <span class="h-6 w-6 rounded-md bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-[10px]" x-text="{{ $index + 1 }}"></span>
                                        <span class="font-bold text-slate-300">{{ $rank->nama_sekolah }}</span>
                                    </div>
                                    <span class="font-bold text-emerald-400">{{ round($rank->avg_waste, 1) }}% <span class="text-[10px] text-slate-500 font-normal">waste</span></span>
                                </div>
                            @endforeach

                            @if(count($schoolRankings) == 0)
                                <div class="p-3 bg-slate-950/40 border border-slate-800/80 rounded-xl flex justify-between items-center">
                                    <div class="flex items-center gap-2">
                                        <span class="h-6 w-6 rounded-md bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-[10px]">1</span>
                                        <span class="font-bold text-slate-300">SDN 2 Mojolangu</span>
                                    </div>
                                    <span class="font-bold text-emerald-400">3.2%</span>
                                </div>
                                <div class="p-3 bg-slate-950/40 border border-slate-800/80 rounded-xl flex justify-between items-center">
                                    <div class="flex items-center gap-2">
                                        <span class="h-6 w-6 rounded-md bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-[10px]">2</span>
                                        <span class="font-bold text-slate-300">SDN 1 Dinoyo</span>
                                    </div>
                                    <span class="font-bold text-emerald-400">9.5%</span>
                                </div>
                                <div class="p-3 bg-slate-950/40 border border-slate-800/80 rounded-xl flex justify-between items-center">
                                    <div class="flex items-center gap-2">
                                        <span class="h-6 w-6 rounded-md bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-[10px]">3</span>
                                        <span class="font-bold text-slate-300">SDN 1 Arjosari</span>
                                    </div>
                                    <span class="font-bold text-emerald-400">11.8%</span>
                                </div>
                                <div class="p-3 bg-slate-950/40 border border-slate-800/80 rounded-xl flex justify-between items-center">
                                    <div class="flex items-center gap-2">
                                        <span class="h-6 w-6 rounded-md bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-[10px]">4</span>
                                        <span class="font-bold text-slate-300">SDN 3 Blimbing</span>
                                    </div>
                                    <span class="font-bold text-rose-400">16.4%</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- AI Insights (7 Cols) -->
                    <div class="lg:col-span-7 p-6 rounded-2xl border shadow-xl flex flex-col justify-between"
                         :class="$store.theme.current === 'dark' ? 'bg-slate-900 border-slate-800' : 'bg-white border-slate-200'">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-xs font-mono font-bold text-purple-400 uppercase tracking-widest">🤖 AI Insight & DSS Recommendations</h3>
                            <span class="text-[9px] font-mono bg-purple-500/10 text-purple-400 border border-purple-500/20 px-2.5 py-1 rounded-full animate-pulse">AI Engine Active</span>
                        </div>
                        <div class="bg-purple-950/10 border border-purple-800/20 p-5 rounded-2xl text-xs leading-relaxed text-slate-300 font-mono tracking-wide">
                            {!! nl2br($aiInsight) !!}
                        </div>
                        <div class="mt-4 text-[9px] font-mono text-slate-500 leading-normal">
                            Analisis di atas dihasilkan otomatis dari histori audit logistik & food waste 7 hari terakhir untuk merekomendasikan kuota porsi optimal di program MBG berikutnya.
                        </div>
                    </div>
                </div>

            </div>
        </template>
    </div>

    <!-- INITIALIZE CHART SCRIPT -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('dashboardState', () => ({
                showCustomizer: false,
                widgets: {!! json_encode($widgetOrder) !!},
                draggedIndex: null,
                saveWidgetOrder() {
                    fetch('{{ route('dashboard.save-widgets') }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({ order: this.widgets })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if(data.success) {
                            this.$store.toasts.add(data.message, 'success');
                        }
                    });
                }
            }));
            
            // 1. Line Chart
            const ctxWaste = document.getElementById('canvasWaste').getContext('2d');
            new Chart(ctxWaste, {
                type: 'line',
                data: {
                    labels: {!! json_encode($chartData['labels']) !!},
                    datasets: [{
                        label: 'Rata-rata Waste (%)',
                        data: {!! json_encode($chartData['trend_waste']) !!},
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.05)',
                        borderWidth: 3,
                        pointBackgroundColor: '#f59e0b',
                        tension: 0.3,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { grid: { color: 'rgba(128,128,128,0.1)' }, ticks: { font: { family: 'monospace', size: 9 }, color: '#94a3b8' } },
                        x: { grid: { color: 'rgba(128,128,128,0.1)' }, ticks: { font: { family: 'monospace', size: 9 }, color: '#94a3b8' } }
                    }
                }
            });

            // 2. Bar Chart
            const ctxDist = document.getElementById('canvasDist').getContext('2d');
            new Chart(ctxDist, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($chartData['labels']) !!},
                    datasets: [{
                        label: 'Porsi Terkirim',
                        data: {!! json_encode($chartData['distribusi_porsi']) !!},
                        backgroundColor: '#3b82f6',
                        borderRadius: 8,
                        barThickness: 16
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { grid: { color: 'rgba(128,128,128,0.1)' }, ticks: { font: { family: 'monospace', size: 9 }, color: '#94a3b8' } },
                        x: { grid: { color: 'rgba(128,128,128,0.1)' }, ticks: { font: { family: 'monospace', size: 9 }, color: '#94a3b8' } }
                    }
                }
            });

            // 3. Pie Chart
            const ctxPie = document.getElementById('canvasPie').getContext('2d');
            new Chart(ctxPie, {
                type: 'doughnut',
                data: {
                    labels: ['Sedikit', 'Sedang', 'Tinggi'],
                    datasets: [{
                        data: [
                            {{ $kategoriWastes['Sedikit'] }},
                            {{ $kategoriWastes['Sedang'] }},
                            {{ $kategoriWastes['Tinggi'] }}
                        ],
                        backgroundColor: ['#10b981', '#f59e0b', '#f43f5e'],
                        borderWidth: 2,
                        borderColor: '#0f172a'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    cutout: '70%'
                }
            });
        });
    </script>

</div>
@endsection