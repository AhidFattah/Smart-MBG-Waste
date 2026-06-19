@extends('Layouts.app')

@section('content')
<div x-data="{ 
    qtyHabis: 0, 
    qtySebagian: 0, 
    qtyTidakHabis: 0,
    beratSisa: 0.0,
    schoolId: '{{ auth()->user()->school_id ?: 1 }}',
    menuId: 1,
    schools: {
        1: { name: 'SDN 1 Arjosari', cap: 180 },
        2: { name: 'SDN 3 Blimbing', cap: 220 },
        3: { name: 'SDN 2 Mojolangu', cap: 150 },
        4: { name: 'SDN 1 Dinoyo', cap: 200 }
    },
    get currentCap() {
        return this.schools[this.schoolId] ? this.schools[this.schoolId].cap : 180;
    },
    get hitungSisaPorsi() {
        return parseInt(this.qtyTidakHabis) + (parseInt(this.qtySebagian) * 0.5);
    },
    get hitungWastePercent() {
        const total = this.currentCap;
        if(total <= 0) return 0;
        const sisa = this.hitungSisaPorsi;
        return Math.min(100, Math.max(0, parseFloat(((sisa / total) * 100).toFixed(2))));
    },
    get wasteCategory() {
        const pct = this.hitungWastePercent;
        if(pct <= 10) return 'Sedikit';
        if(pct <= 20) return 'Sedang';
        return 'Tinggi';
    }
}">

    <!-- HEADER FORM -->
    <div class="mb-8">
        <h1 class="text-2xl font-black uppercase tracking-tight" :class="$store.theme.current === 'dark' ? 'text-white' : 'text-slate-900'">
            Pencatatan Food Waste Harian
        </h1>
        <p class="text-xs font-mono" :class="$store.theme.current === 'dark' ? 'text-slate-400' : 'text-slate-500'">
            Input harian kuantitas porsi sisa dari sekolah sasaran untuk analisis DSS kuota hari berikutnya
        </p>
    </div>

    <!-- LAYOUT SPLIT -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- KOLOM KIRI: Info Kapasitas & Node Sekolah -->
        <div class="lg:col-span-5 space-y-6">
            
            <div class="p-6 rounded-2xl border shadow-xl"
                 :class="$store.theme.current === 'dark' ? 'bg-slate-900 border-slate-800' : 'bg-white border-slate-200'">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xs font-mono font-bold text-blue-400 uppercase tracking-wider">🏢 Sekolah Sasaran Aktif</h3>
                    <span class="text-[9px] font-mono bg-blue-500/10 text-blue-400 px-2 py-0.5 rounded border border-blue-500/20">4 Node Terdaftar</span>
                </div>
                
                <div class="space-y-2 font-mono text-xs">
                    <template x-for="(sch, id) in schools" :key="id">
                        <div @click="if(!{{ auth()->user()->school_id ?: 0 }}) schoolId = id"
                             :class="schoolId == id ? 'border-emerald-500 bg-emerald-500/5' : 'border-slate-800 bg-slate-950/20'"
                             class="border p-3 rounded-xl flex justify-between items-center hover:border-emerald-500/50 transition cursor-pointer">
                            <span class="font-bold" :class="schoolId == id ? 'text-emerald-400' : 'text-slate-300'" x-text="sch.name"></span>
                            <span class="text-[10px] text-slate-500 bg-slate-950 px-2.5 py-1 rounded" x-text="'Kapasitas: ' + sch.cap + ' Porsi'"></span>
                        </div>
                    </template>
                </div>
            </div>

            <div class="p-6 rounded-2xl border shadow-xl text-xs font-mono"
                 :class="$store.theme.current === 'dark' ? 'bg-slate-900 border-slate-800 text-slate-300' : 'bg-white border-slate-200 text-slate-800'">
                <h3 class="text-xs font-bold uppercase tracking-wider mb-3" :class="$store.theme.current === 'dark' ? 'text-slate-400' : 'text-slate-700'">Detail Manifes Dapur</h3>
                <div class="p-4 rounded-xl space-y-2" :class="$store.theme.current === 'dark' ? 'bg-slate-950/50' : 'bg-slate-50'">
                    <p>🗓️ <span class="text-slate-500">Tanggal Operasional:</span> <span class="font-bold">{{ date('d M Y') }}</span></p>
                    <p>🕒 <span class="text-slate-500">Batas Waktu Input:</span> <span class="text-amber-500 font-bold">14:00 WIB</span></p>
                    <p>📦 <span class="text-slate-500">Status Dapur:</span> <span class="text-emerald-400 font-bold">Didistribusikan</span></p>
                </div>
            </div>
        </div>

        <!-- KOLOM KANAN: Form Input Sisa Makanan -->
        <div class="lg:col-span-7 p-6 rounded-2xl border shadow-xl"
             :class="$store.theme.current === 'dark' ? 'bg-slate-900 border-slate-800 text-white' : 'bg-white border-slate-200 text-slate-800'">
            
            <h3 class="text-xs font-mono font-bold text-amber-400 uppercase tracking-wider mb-4">📝 Form Evaluasi Sisa Makanan</h3>
            
            <form action="{{ route('sekolah.waste.store') }}" method="POST" class="space-y-4 font-mono text-xs">
                @csrf
                
                <!-- 1. Pilihan Sekolah (Kunci jika Petugas Sekolah) -->
                <div>
                    <label class="block text-slate-400 mb-1 font-bold">1. Sekolah Sasaran Anda</label>
                    @if(auth()->user()->school_id)
                        <input type="hidden" name="school_id" value="{{ auth()->user()->school_id }}">
                        <input type="text" readonly value="{{ $schools->where('id', auth()->user()->school_id)->first()->name ?? 'Sekolah Anda' }}" 
                               class="w-full bg-slate-950 border border-slate-800/80 rounded-xl px-4 py-2.5 text-slate-400 font-bold focus:outline-none">
                    @else
                        <select name="school_id" x-model="schoolId" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2.5 text-white focus:outline-none focus:border-emerald-500 cursor-pointer">
                            @foreach($schools as $sch)
                                <option value="{{ $sch->id }}">{{ $sch->name }}</option>
                            @endforeach
                        </select>
                    @endif
                </div>

                <!-- 2. Variasi Menu -->
                <div>
                    <label class="block text-slate-400 mb-1 font-bold">2. Variasi Menu Terkonsumsi Hari Ini</label>
                    <select name="menu_id" x-model="menuId" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2.5 text-white focus:outline-none focus:border-emerald-500 cursor-pointer">
                        @foreach($menus as $m)
                            <option value="{{ $m->id }}">{{ $m->menu_name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- 3. Tanggal Input -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-slate-400 mb-1 font-bold">3. Tanggal Distribusi</label>
                        <input type="date" name="tanggal_distribusi" value="{{ date('Y-m-d') }}" required class="w-full bg-slate-950 border border-slate-800 text-white rounded-xl px-3 py-2.5 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-slate-400 mb-1 font-bold">4. Proyeksi Koreksi</label>
                        <select class="w-full bg-slate-950 border border-slate-800 text-amber-400 font-bold rounded-xl px-3 py-2.5 focus:outline-none">
                            <option value="today">Sisa Hari Ini (Real-Time)</option>
                            <option value="tomorrow">Kalkulasi Untuk Besok</option>
                        </select>
                    </div>
                </div>

                <hr class="border-slate-800 my-2">

                <!-- 4. Kondisi Riil Box Makanan -->
                <div class="p-4 rounded-xl border" :class="$store.theme.current === 'dark' ? 'bg-slate-950/40 border-slate-800' : 'bg-slate-50 border-slate-200'">
                    <h4 class="font-bold text-xs mb-3 text-slate-400">Audit Kondisi Riil Box Makanan di Kelas</h4>
                    
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-slate-400 mb-1 text-[10px]">Porsi Habis (Pcs)</label>
                            <input type="number" name="qty_habis" x-model="qtyHabis" min="0" required 
                                   class="w-full bg-slate-900 border border-slate-800 text-emerald-400 font-bold rounded-xl px-3 py-2 focus:outline-none focus:border-emerald-500 text-center">
                        </div>
                        <div>
                            <label class="block text-slate-400 mb-1 text-[10px]">Sisa Sebagian (Pcs)</label>
                            <input type="number" name="qty_sebagian" x-model="qtySebagian" min="0" required 
                                   class="w-full bg-slate-900 border border-slate-800 text-amber-400 font-bold rounded-xl px-3 py-2 focus:outline-none focus:border-amber-500 text-center">
                        </div>
                        <div>
                            <label class="block text-slate-400 mb-1 text-[10px]">Sisa Total/Utuh (Pcs)</label>
                            <input type="number" name="qty_tidak_habis" x-model="qtyTidakHabis" min="0" required 
                                   class="w-full bg-slate-900 border border-slate-800 text-rose-400 font-bold rounded-xl px-3 py-2 focus:outline-none focus:border-rose-500 text-center">
                        </div>
                    </div>
                </div>

                <!-- 5. Berat Sisa & Faktor Penyebab -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-slate-400 mb-1 font-bold">5. Berat Total Sisa (Kg)</label>
                        <input type="number" name="berat_sisa_makanan" x-model="beratSisa" step="0.01" required placeholder="Contoh: 4.5"
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-emerald-500">
                    </div>
                    
                    <div>
                        <label class="block text-slate-400 mb-1 font-bold">6. Faktor Penyebab Food Waste</label>
                        <select name="faktor_penyebab" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2.5 text-white focus:outline-none focus:border-emerald-500 cursor-pointer">
                            <option value="Rasa Kurang Cocok">Rasa Hidangan Kurang Cocok (Hambar/Pedas)</option>
                            <option value="Porsi Terlalu Banyak">Volume Porsi Terlalu Banyak (Kekenyangan)</option>
                            <option value="Jam Makan Singkat">Jam Makan Berbenturan dengan Istirahat Singkat</option>
                            <option value="Membawa Bekal">Anak Membawa Bekal Sendiri dari Rumah</option>
                        </select>
                    </div>
                </div>

                <!-- Live Auto-tagging Calculator Output -->
                <div class="p-4 rounded-xl border flex items-center justify-between font-mono-tech"
                     :class="$store.theme.current === 'dark' ? 'bg-slate-950/80 border-slate-800' : 'bg-slate-50 border-slate-200'">
                    <div>
                        <p class="text-[10px] text-slate-500">Indeks Sisa (Auto-Kalkulasi)</p>
                        <p class="text-[9px] text-slate-400 mt-0.5">Sisa Setara: <span class="font-bold text-white" x-text="hitungSisaPorsi"></span> Porsi</p>
                    </div>
                    <div class="text-right">
                        <span class="text-xl font-black" :class="wasteCategory === 'Tinggi' ? 'text-rose-400' : (wasteCategory === 'Sedang' ? 'text-amber-400' : 'text-emerald-400')" x-text="hitungWastePercent + '%'"></span>
                        <span class="ml-2 px-2 py-0.5 text-[9px] rounded font-bold" 
                              :class="wasteCategory === 'Tinggi' ? 'bg-rose-500/10 text-rose-400 border border-rose-500/20' : (wasteCategory === 'Sedang' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20')" 
                              x-text="wasteCategory.toUpperCase()"></span>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" 
                            class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold py-3 rounded-xl transition cursor-pointer tracking-wider uppercase text-center transform active:scale-99">
                        💾 SIMPAN LOG & UPDATE PROYEKSI BESOK
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection