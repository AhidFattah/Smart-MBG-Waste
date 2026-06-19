@extends('Layouts.app')

@section('content')
<div>
    <!-- HEADER -->
    <div class="mb-8">
        <h1 class="text-2xl font-black uppercase tracking-tight" :class="$store.theme.current === 'dark' ? 'text-white' : 'text-slate-900'">
            Riwayat Log Food Waste Sekolah
        </h1>
        <p class="text-xs font-mono" :class="$store.theme.current === 'dark' ? 'text-slate-400' : 'text-slate-500'">
            Daftar audit sisa makanan dan rekomendasi penyesuaian porsi sekolah setempat
        </p>
    </div>

    <!-- DATA TABLE -->
    <div class="rounded-2xl border overflow-hidden shadow-xl"
         :class="$store.theme.current === 'dark' ? 'bg-slate-900 border-slate-800/80' : 'bg-white border-slate-200'">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b font-mono uppercase tracking-wider text-[10px]"
                        :class="$store.theme.current === 'dark' ? 'bg-slate-950 border-slate-800 text-slate-400' : 'bg-slate-100 border-slate-200 text-slate-500'">
                        <th class="p-4">Tanggal</th>
                        <th class="p-4">Sekolah Sasaran</th>
                        <th class="p-4">Menu Terdistribusi</th>
                        <th class="p-4 text-center">Porsi Dikirim</th>
                        <th class="p-4 text-right">Berat Sisa (Kg)</th>
                        <th class="p-4 text-center">Sisa Setara</th>
                        <th class="p-4 text-center">Indeks Waste (%)</th>
                        <th class="p-4 text-center">Kategori</th>
                        <th class="p-4">Faktor Penyebab</th>
                    </tr>
                </thead>
                <tbody class="divide-y" :class="$store.theme.current === 'dark' ? 'divide-slate-800' : 'divide-slate-100'">
                    @forelse($wastes as $w)
                        <tr class="hover:bg-slate-500/5 transition">
                            <td class="p-4 font-mono text-slate-400">{{ date('d M Y', strtotime($w->tanggal_distribusi)) }}</td>
                            <td class="p-4 font-bold text-white">{{ $w->nama_sekolah }}</td>
                            <td class="p-4 text-slate-300 font-mono">{{ $w->nama_menu }}</td>
                            <td class="p-4 text-center font-mono font-bold text-blue-400">{{ $w->total_porsi_dikirim }} Pcs</td>
                            <td class="p-4 text-right font-mono font-bold text-slate-300">{{ $w->berat_sisa_makanan }} Kg</td>
                            <td class="p-4 text-center font-mono font-bold text-slate-300">{{ $w->jumlah_sisa_porsi }} Pcs</td>
                            <td class="p-4 text-center">
                                <span class="px-2 py-0.5 rounded font-bold font-mono" 
                                      :class="$w->persentase_waste > 15 ? 'bg-rose-500/10 text-rose-400 border border-rose-500/20' : ($w->persentase_waste < 5 ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-amber-500/10 text-amber-400 border border-amber-500/20')">
                                    {{ $w->persentase_waste }}%
                                </span>
                            </td>
                            <td class="p-4 text-center">
                                @if($w->kategori_waste == 'Sedikit')
                                    <span class="text-[9px] font-bold font-mono px-2 py-0.5 rounded-lg bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">SEDIKIT</span>
                                @elseif($w->kategori_waste == 'Sedang')
                                    <span class="text-[9px] font-bold font-mono px-2 py-0.5 rounded-lg bg-amber-500/10 text-amber-400 border border-amber-500/20">SEDANG</span>
                                @else
                                    <span class="text-[9px] font-bold font-mono px-2 py-0.5 rounded-lg bg-rose-500/10 text-rose-400 border border-rose-500/20">TINGGI</span>
                                @endif
                            </td>
                            <td class="p-4 text-slate-400 font-mono truncate max-w-xs">{{ $w->penyebab_waste }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="p-8 text-center text-slate-500 font-mono">📭 Belum ada riwayat input food waste tercatat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
