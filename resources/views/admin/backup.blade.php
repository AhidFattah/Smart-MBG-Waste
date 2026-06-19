@extends('Layouts.app')

@section('content')
<div>
    <!-- HEADER -->
    <div class="mb-8">
        <h1 class="text-2xl font-black uppercase tracking-tight" :class="$store.theme.current === 'dark' ? 'text-white' : 'text-slate-900'">
            Cadangan & Pemulihan Basis Data
        </h1>
        <p class="text-xs font-mono" :class="$store.theme.current === 'dark' ? 'text-slate-400' : 'text-slate-500'">
            Amankan data transaksi logistik, distribusi, dan food waste dengan backup portabel JSON
        </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        
        <!-- RUN BACKUP PANEL -->
        <div class="lg:col-span-1 p-6 rounded-2xl border flex flex-col justify-between"
             :class="$store.theme.current === 'dark' ? 'bg-slate-900 border-slate-800 text-white' : 'bg-white border-slate-200 text-slate-800 shadow-md'">
            <div>
                <h3 class="text-xs font-mono font-bold text-emerald-400 uppercase tracking-wider mb-3">💾 Cadangkan Basis Data</h3>
                <p class="text-[11px] text-slate-400 leading-relaxed font-mono">
                    Cadangkan semua master data, log transaksi gudang, manifes pengiriman kargo, dan riwayat sisa makanan secara portabel. File cadangan akan diunduh dalam bentuk file JSON terenkripsi sistem.
                </p>
            </div>
            
            <a href="{{ route('admin.backup.run') }}" 
               class="mt-6 w-full bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-500 hover:to-emerald-600 text-white text-center font-bold py-3 px-4 rounded-xl text-xs uppercase tracking-wider shadow-lg transition transform active:scale-99 block">
                BUAT CADANGAN BARU
            </a>
        </div>

        <!-- UPLOAD RESTORE PANEL -->
        <div class="lg:col-span-2 p-6 rounded-2xl border flex flex-col justify-between"
             :class="$store.theme.current === 'dark' ? 'bg-slate-900 border-slate-800 text-white' : 'bg-white border-slate-200 text-slate-800 shadow-md'">
            <div>
                <h3 class="text-xs font-mono font-bold text-amber-400 uppercase tracking-wider mb-3">🔄 Pulihkan Basis Data (Restore)</h3>
                <p class="text-[11px] text-slate-400 leading-relaxed font-mono mb-4">
                    Unggah file cadangan berformat JSON untuk mengembalikan semua data ke kondisi cadangan sebelumnya. 
                    <strong class="text-amber-500">Peringatan:</strong> Proses restore akan menghapus data saat ini untuk digantikan dengan data cadangan!
                </p>
            </div>

            <form action="{{ route('admin.backup.restore') }}" method="POST" enctype="multipart/form-data" class="space-y-4 font-mono text-xs">
                @csrf
                <div class="flex flex-col sm:flex-row gap-2">
                    <input type="file" name="backup_file" required 
                           class="flex-1 bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-white focus:outline-none">
                    <button type="submit" onclick="return confirm('Apakah Anda yakin ingin memulihkan database? Seluruh data aktif saat ini akan diganti!')"
                            class="bg-amber-600 hover:bg-amber-500 text-white font-bold py-2.5 px-6 rounded-xl uppercase tracking-wider transition cursor-pointer">
                        RESTORE DATA
                    </button>
                </div>
            </form>
        </div>

    </div>

    <!-- BACKUP FILES LIST -->
    <div class="rounded-2xl border overflow-hidden shadow-xl"
         :class="$store.theme.current === 'dark' ? 'bg-slate-900 border-slate-800/80' : 'bg-white border-slate-200'">
        <div class="p-4 bg-slate-950/40 border-b border-slate-800/80 flex justify-between items-center text-xs font-mono">
            <span class="text-slate-400 font-bold">Daftar Berkas Cadangan Terdaftar (Local Storage)</span>
            <span class="text-[9px] bg-blue-500/10 text-blue-400 border border-blue-500/20 px-2 py-0.5 rounded">Directory: storage/app/backups</span>
        </div>

        <div class="overflow-x-auto text-xs">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b font-mono uppercase tracking-wider text-[10px]"
                        :class="$store.theme.current === 'dark' ? 'bg-slate-950 border-slate-800 text-slate-400' : 'bg-slate-100 border-slate-200 text-slate-500'">
                        <th class="p-4">Nama File Backup</th>
                        <th class="p-4">Ukuran File</th>
                        <th class="p-4">Tanggal Dibuat</th>
                        <th class="p-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y" :class="$store.theme.current === 'dark' ? 'divide-slate-800' : 'divide-slate-100'">
                    @forelse($backups as $back)
                        <tr class="hover:bg-slate-500/5 transition font-mono">
                            <td class="p-4 font-bold text-white">{{ $back['filename'] }}</td>
                            <td class="p-4 text-slate-300">{{ $back['size'] }}</td>
                            <td class="p-4 text-slate-400">{{ $back['created_at'] }}</td>
                            <td class="p-4 text-center">
                                <div class="flex items-center justify-center gap-1.5 text-[10px]">
                                    <!-- Download -->
                                    <a href="{{ route('admin.backup.download', $back['filename']) }}" 
                                       class="text-emerald-400 hover:text-emerald-300 bg-emerald-500/5 px-3 py-1.5 rounded border border-emerald-500/10 transition">
                                        UNDUH
                                    </a>

                                    <!-- Delete -->
                                    <form action="{{ route('admin.backup.delete', $back['filename']) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus file cadangan ini secara permanen?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-rose-500 hover:text-rose-400 bg-rose-500/5 px-3 py-1.5 rounded border border-rose-500/10 transition cursor-pointer">
                                            HAPUS DUMP
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="p-8 text-center text-slate-500 font-mono">📭 Belum ada berkas cadangan database yang tersimpan di server.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
