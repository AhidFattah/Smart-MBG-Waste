@extends('Layouts.app')

@section('content')
<div x-data="{ 
    modalTambah: false, 
    modalStatus: false, 
    modalQR: false,
    selectedDist: {},
    siswaHadir: 180,
    cadanganPercent: 3.00,
    waktuDiterima: '',
    penerimaNama: '',
    statusPengiriman: 'Diterima',
    get hitungPorsi() {
        return Math.ceil(parseInt(this.siswaHadir) + (parseInt(this.siswaHadir) * parseFloat(this.cadanganPercent) / 100));
    }
}">

    <!-- HEADER -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-black uppercase tracking-tight" :class="$store.theme.current === 'dark' ? 'text-white' : 'text-slate-900'">
                Pengiriman & Distribusi MBG
            </h1>
            <p class="text-xs font-mono" :class="$store.theme.current === 'dark' ? 'text-slate-400' : 'text-slate-500'">
                Manifes logistik kargo katering gizi harian dari dapur pusat ke sekolah sasaran
            </p>
        </div>

        <button @click="modalTambah = true" 
                class="bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold font-mono px-4 py-2.5 rounded-xl shadow-lg transition cursor-pointer flex items-center gap-2">
            <i data-lucide="plus" class="h-4 w-4"></i>
            GENERASI DISTRIBUSI BARU
        </button>
    </div>

    <!-- DATA TABLE -->
    <div class="rounded-2xl border overflow-hidden shadow-xl"
         :class="$store.theme.current === 'dark' ? 'bg-slate-900 border-slate-800/80' : 'bg-white border-slate-200'">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b font-mono uppercase tracking-wider text-[10px]"
                        :class="$store.theme.current === 'dark' ? 'bg-slate-950 border-slate-800 text-slate-400' : 'bg-slate-100 border-slate-200 text-slate-500'">
                        <th class="p-4">Kode QR</th>
                        <th class="p-4">Tanggal</th>
                        <th class="p-4">Sekolah Sasaran</th>
                        <th class="p-4">Menu Terdistribusi</th>
                        <th class="p-4 text-center">Siswa Hadir</th>
                        <th class="p-4 text-center">Kirim Porsi</th>
                        <th class="p-4">Petugas / Kurir</th>
                        <th class="p-4">Status Pengiriman</th>
                        <th class="p-4 text-center">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y" :class="$store.theme.current === 'dark' ? 'divide-slate-800' : 'divide-slate-100'">
                    @forelse($distributions as $dist)
                        <tr class="hover:bg-slate-500/5 transition">
                            <!-- QR Code Clickable -->
                            <td class="p-4 font-mono font-bold text-emerald-400">
                                <button @click="selectedDist = {{ json_encode($dist) }}; modalQR = true" class="hover:underline flex items-center gap-1.5 cursor-pointer">
                                    <i data-lucide="qr-code" class="h-4 w-4 shrink-0"></i>
                                    {{ $dist->qr_code_surat_jalan ?: 'GENERATE-QR' }}
                                </button>
                            </td>
                            <td class="p-4 font-mono text-slate-400">{{ date('d M Y', strtotime($dist->tanggal_distribusi)) }}</td>
                            <td class="p-4 font-bold text-white">{{ $dist->nama_sekolah }}</td>
                            <td class="p-4 text-slate-300 font-mono">{{ $dist->nama_menu }}</td>
                            <td class="p-4 text-center font-mono font-bold text-slate-400">{{ $dist->jumlah_siswa_hadir ?: $dist->total_porsi_dikirim }} anak</td>
                            <td class="p-4 text-center font-mono font-bold text-blue-400">
                                {{ $dist->total_porsi_dikirim }} <span class="text-[10px] text-slate-500 font-normal">Porsi</span>
                            </td>
                            <td class="p-4 text-slate-300 font-mono">
                                <p class="font-bold">{{ $dist->petugas_distribusi ?: 'Kurir A' }}</p>
                                <p class="text-[10px] text-slate-500">{{ $dist->kendaraan_distribusi ?: 'Roda Tiga' }}</p>
                            </td>
                            <td class="p-4">
                                @if($dist->status_pengiriman == 'Diterima')
                                    <span class="text-[9px] font-bold font-mono px-2 py-0.5 rounded-lg bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">DITERIMA</span>
                                    @if($dist->penerima_nama)
                                        <p class="text-[9px] font-mono text-slate-500 mt-1">Oleh: {{ $dist->penerima_nama }}</p>
                                    @endif
                                @elseif($dist->status_pengiriman == 'Dalam_Perjalanan')
                                    <span class="text-[9px] font-bold font-mono px-2 py-0.5 rounded-lg bg-blue-500/10 text-blue-400 border border-blue-500/20">DI JALAN</span>
                                @elseif($dist->status_pengiriman == 'Diproses')
                                    <span class="text-[9px] font-bold font-mono px-2 py-0.5 rounded-lg bg-amber-500/10 text-amber-400 border border-amber-500/20">DIPROSES</span>
                                @else
                                    <span class="text-[9px] font-bold font-mono px-2 py-0.5 rounded-lg bg-rose-500/10 text-rose-400 border border-rose-500/20">GAGAL</span>
                                @endif
                            </td>
                            <td class="p-4 text-center">
                                <div class="flex items-center justify-center gap-1.5 text-[10px] font-mono">
                                    <!-- Print Surat Jalan -->
                                    <a href="{{ route('dapur.distributions.surat-jalan', $dist->id) }}" target="_blank" 
                                       class="text-amber-400 hover:text-amber-300 bg-amber-500/5 px-2 py-1.5 rounded border border-amber-500/10 transition">
                                        SURAT JALAN
                                    </a>

                                    <button @click="
                                        selectedDist = {{ json_encode($dist) }};
                                        statusPengiriman = selectedDist.status_pengiriman;
                                        penerimaNama = selectedDist.penerima_nama || '';
                                        modalStatus = true;
                                    " class="text-blue-400 hover:text-blue-300 bg-blue-500/5 px-2 py-1.5 rounded border border-blue-500/10 transition cursor-pointer">
                                        STATUS
                                    </button>

                                    <form action="{{ route('dapur.distributions.destroy', $dist->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data distribusi ini?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-rose-500 hover:text-rose-400 bg-rose-500/5 px-2 py-1.5 rounded border border-rose-500/10 transition cursor-pointer">
                                            HAPUS
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="p-8 text-center text-slate-500 font-mono">📭 Belum ada riwayat pengiriman hari ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL TAMBAH DISTRIBUSI (DYNAMIC FORM CALCULATOR & FIFO WARNINGS) -->
    <div x-show="modalTambah" class="fixed inset-0 bg-black/70 backdrop-blur-xs flex items-center justify-center p-4 z-50" x-transition>
        <div class="bg-slate-900 border border-slate-800 w-full max-w-md rounded-2xl overflow-hidden shadow-2xl p-6">
            <div class="flex justify-between items-center border-b border-slate-800 pb-3 mb-4">
                <h3 class="text-xs font-mono font-bold text-white uppercase tracking-wider">📦 Generasi Otomatis Tiket Distribusi</h3>
                <button @click="modalTambah = false" class="text-slate-400 hover:text-white cursor-pointer text-lg">&times;</button>
            </div>
            
            <form action="{{ route('dapur.distributions.store') }}" method="POST" class="space-y-4 font-mono text-xs text-slate-300">
                @csrf
                
                <div>
                    <label class="block text-slate-400 mb-1 font-bold">1. Pilih Sekolah Sasaran</label>
                    <select name="school_id" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2.5 text-white focus:outline-none focus:border-emerald-500 cursor-pointer">
                        @foreach($schools as $sch)
                            <option value="{{ $sch->id }}">{{ $sch->name }} (Kapasitas: {{ $sch->total_students }} Siswa)</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-slate-400 mb-1 font-bold">2. Pilih Menu Rotasi</label>
                    <select name="menu_id" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2.5 text-white focus:outline-none focus:border-emerald-500 cursor-pointer">
                        @foreach($menus as $menu)
                            <option value="{{ $menu->id }}">{{ $menu->menu_name }} ({{ $menu->calories }} kkal)</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-400 mb-1 font-bold">3. Jumlah Siswa Hadir</label>
                        <input type="number" name="jumlah_siswa_hadir" x-model="siswaHadir" required 
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-white focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-slate-400 mb-1 font-bold">4. Cadangan Porsi (%)</label>
                        <input type="number" name="persentase_cadangan" x-model="cadanganPercent" step="0.1" required 
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-white focus:outline-none focus:border-emerald-500">
                    </div>
                </div>

                <!-- Live Portion Calculator Output Panel -->
                <div class="p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-xl flex items-center justify-between">
                    <div>
                        <p class="text-[10px] text-slate-400 uppercase">Kalkulasi Otomatis Porsi Gizi</p>
                        <p class="text-[9px] text-slate-500 mt-0.5">Rumus: Siswa Hadir + (Siswa Hadir × Cadangan %)</p>
                    </div>
                    <div class="text-right">
                        <span class="text-xl font-black text-emerald-400 font-mono-tech" x-text="hitungPorsi"></span>
                        <span class="text-[9px] text-slate-500 font-bold ml-1">Porsi</span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-400 mb-1">Tanggal Kirim</label>
                        <input type="date" name="tanggal_distribusi" value="{{ date('Y-m-d') }}" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-slate-400 mb-1">Jam Berangkat</label>
                        <input type="time" name="waktu_distribusi" value="08:30" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-white focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-400 mb-1">Nama Petugas / Driver</label>
                        <input type="text" name="petugas_distribusi" placeholder="Joni Setiawan" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-white focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-slate-400 mb-1">Kendaraan / No. Plat</label>
                        <input type="text" name="kendaraan_distribusi" placeholder="Mobil Box A (N 1234 AB)" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-white focus:outline-none">
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-4 border-t border-slate-800 mt-6">
                    <button type="button" @click="modalTambah = false" class="bg-slate-950 hover:bg-slate-800 border border-slate-800 text-slate-400 px-4 py-2 rounded-xl cursor-pointer">BATAL</button>
                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-500 text-white px-4 py-2 rounded-xl font-bold cursor-pointer">KIRIM MAKANAN</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL EDIT STATUS PENGIRIMAN -->
    <div x-show="modalStatus" class="fixed inset-0 bg-black/70 backdrop-blur-xs flex items-center justify-center p-4 z-50" x-transition>
        <div class="bg-slate-900 border border-slate-800 w-full max-w-sm rounded-2xl overflow-hidden shadow-2xl p-6">
            <div class="flex justify-between items-center border-b border-slate-800 pb-3 mb-4">
                <h3 class="text-xs font-mono font-bold text-white uppercase tracking-wider">✏️ Update Status Pengiriman Kargo</h3>
                <button @click="modalStatus = false" class="text-slate-400 hover:text-white cursor-pointer text-lg">&times;</button>
            </div>
            
            <form :action="'{{ route('dapur.distributions.update-status', '') }}/' + selectedDist.id" method="POST" class="space-y-4 font-mono text-xs text-slate-300">
                @csrf
                
                <div>
                    <label class="block text-slate-400 mb-1 font-bold">Status Kurir</label>
                    <select name="status_pengiriman" x-model="statusPengiriman" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-white focus:outline-none cursor-pointer">
                        <option value="Diproses">Diproses Dapur</option>
                        <option value="Dalam_Perjalanan">Dalam Perjalanan (Logistik)</option>
                        <option value="Diterima">Diterima Sekolah (Sukses)</option>
                        <option value="Gagal">Gagal Kirim (Terganggu)</option>
                    </select>
                </div>

                <div x-show="statusPengiriman === 'Diterima'">
                    <label class="block text-slate-400 mb-1">Nama Penerima Sekolah (NIP/Petugas)</label>
                    <input type="text" name="penerima_nama" x-model="penerimaNama" placeholder="Siti Aminah, S.Pd." class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-white focus:outline-none">
                </div>

                <div class="flex justify-end gap-2 pt-4 border-t border-slate-800 mt-6">
                    <button type="button" @click="modalStatus = false" class="bg-slate-950 hover:bg-slate-800 border border-slate-800 text-slate-400 px-4 py-2 rounded-xl cursor-pointer">BATAL</button>
                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-500 text-white px-4 py-2 rounded-xl font-bold cursor-pointer">UPDATE STATUS</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL TIKET QR SURAT JALAN -->
    <div x-show="modalQR" class="fixed inset-0 bg-black/75 backdrop-blur-xs flex items-center justify-center p-4 z-50" x-transition>
        <div class="bg-slate-900 border border-slate-800 w-full max-w-xs rounded-2xl overflow-hidden shadow-2xl p-6 text-center">
            <div class="flex justify-between items-center border-b border-slate-800 pb-3 mb-4">
                <h3 class="text-xs font-mono font-bold text-white uppercase tracking-wider">🎫 Tiket QR Distribusi</h3>
                <button @click="modalQR = false" class="text-slate-400 hover:text-white cursor-pointer text-lg">&times;</button>
            </div>
            
            <div class="bg-white p-6 rounded-2xl inline-block mb-4 shadow-inner">
                <!-- Mockup QR Code Visual -->
                <div class="w-36 h-36 border-4 border-slate-900 p-2 flex flex-col justify-between items-center relative">
                    <div class="w-8 h-8 border-4 border-slate-900 absolute top-0 left-0 bg-slate-900"></div>
                    <div class="w-8 h-8 border-4 border-slate-900 absolute top-0 right-0 bg-slate-900"></div>
                    <div class="w-8 h-8 border-4 border-slate-900 absolute bottom-0 left-0 bg-slate-900"></div>
                    <div class="text-[9px] font-black text-slate-900 font-mono tracking-widest leading-none mt-10">SMART MBG</div>
                    <div class="text-[7px] text-slate-500 font-mono" x-text="selectedDist.qr_code_surat_jalan"></div>
                </div>
            </div>

            <p class="text-xs font-mono font-bold text-emerald-400" x-text="selectedDist.nama_sekolah"></p>
            <p class="text-[10px] font-mono text-slate-500 mt-1" x-text="'Porsi Kirim: ' + selectedDist.total_porsi_dikirim + ' Box'"></p>

            <button type="button" @click="modalQR = false" class="mt-6 w-full bg-slate-950 border border-slate-800 text-slate-400 py-2.5 rounded-xl cursor-pointer font-mono text-xs">TUTUP</button>
        </div>
    </div>

</div>
@endsection
