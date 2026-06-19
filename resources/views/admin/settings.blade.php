@extends('Layouts.app')

@section('content')
<div>
    <!-- HEADER -->
    <div class="mb-8">
        <h1 class="text-2xl font-black uppercase tracking-tight" :class="$store.theme.current === 'dark' ? 'text-white' : 'text-slate-900'">
            Pengaturan Sistem
        </h1>
        <p class="text-xs font-mono" :class="$store.theme.current === 'dark' ? 'text-slate-400' : 'text-slate-500'">
            Konfigurasi parameter operasional, ambang batas DSS, dan kustomisasi tampilan umum
        </p>
    </div>

    <!-- FORM CONFIGURATION -->
    <div class="max-w-3xl bg-slate-900/40 p-6 rounded-2xl border shadow-xl font-mono text-xs"
         :class="$store.theme.current === 'dark' ? 'bg-slate-900 border-slate-800 text-white' : 'bg-white border-slate-200 text-slate-800'">
        
        <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6">
            @csrf
            
            <!-- Group Umum -->
            <div>
                <h3 class="text-xs font-bold text-emerald-400 uppercase tracking-wider mb-4 border-b border-slate-800 pb-2">🏢 Parameter Instansi & Aplikasi</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-slate-400 mb-1">Nama Aplikasi</label>
                        <input type="text" name="app_name" value="{{ $settings['app_name'] ?? 'Smart MBG Waste' }}" 
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-slate-400 mb-1">Bahasa Bawaan (Default Locale)</label>
                        <select name="default_lang" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2.5 text-white focus:outline-none cursor-pointer">
                            <option value="id" {{ ($settings['default_lang'] ?? '') == 'id' ? 'selected' : '' }}>Bahasa Indonesia (ID)</option>
                            <option value="en" {{ ($settings['default_lang'] ?? '') == 'en' ? 'selected' : '' }}>English (EN)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Group DSS -->
            <div>
                <h3 class="text-xs font-bold text-purple-400 uppercase tracking-wider mb-4 border-b border-slate-800 pb-2">🤖 Konfigurasi DSS (Decision Support System)</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-slate-400 mb-1">Ambang Batas Waste Tinggi (%)</label>
                        <input type="number" name="threshold_waste_high" value="{{ $settings['threshold_waste_high'] ?? 15 }}" 
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-emerald-500">
                        <span class="text-[9px] text-slate-500 mt-1 block">Rata-rata di atas batas ini memicu saran pemangkasan porsi.</span>
                    </div>
                    <div>
                        <label class="block text-slate-400 mb-1">Ambang Batas Waste Rendah (%)</label>
                        <input type="number" name="threshold_waste_low" value="{{ $settings['threshold_waste_low'] ?? 5 }}" 
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-emerald-500">
                        <span class="text-[9px] text-slate-500 mt-1 block">Rata-rata di bawah batas ini memicu saran penambahan porsi.</span>
                    </div>
                </div>
            </div>

            <!-- Group Distribusi -->
            <div>
                <h3 class="text-xs font-bold text-blue-400 uppercase tracking-wider mb-4 border-b border-slate-800 pb-2">📦 Parameter Distribusi & Logistik</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-slate-400 mb-1">Persentase Buffer Cadangan Makanan (%)</label>
                        <input type="number" name="buffer_percentage" value="{{ $settings['buffer_percentage'] ?? 3 }}" 
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-emerald-500">
                        <span class="text-[9px] text-slate-500 mt-1 block">Rasio porsi cadangan tambahan bawaan dari jumlah siswa hadir.</span>
                    </div>
                    <div>
                        <label class="block text-slate-400 mb-1">Tema Tampilan Bawaan</label>
                        <select name="theme" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2.5 text-white focus:outline-none cursor-pointer">
                            <option value="dark" {{ ($settings['theme'] ?? '') == 'dark' ? 'selected' : '' }}>Sleek Dark Mode (Default)</option>
                            <option value="light" {{ ($settings['theme'] ?? '') == 'light' ? 'selected' : '' }}>Clean Light Mode</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-800 flex justify-end">
                <button type="submit" 
                        class="bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold py-3 px-6 rounded-xl transition cursor-pointer tracking-wider uppercase text-center transform active:scale-99">
                    💾 SIMPAN PERUBAHAN PARAMETER
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
