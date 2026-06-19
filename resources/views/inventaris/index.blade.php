@extends('Layouts.app')

@section('content')
<div x-data="{ 
    modalTambah: false, 
    modalEdit: false, 
    modalBarcode: false,
    barcodeScanned: '',
    selectedBahan: {},
    checkBarcode() {
        // Simulator camera scanner pencari kode barcode
        const matched = {
            '8991234567890': { name: 'Beras Pandan Wangi', cat: 'Beras', unit: 'Kg', price: 14000 },
            '8991234567891': { name: 'Daging Ayam Fillet', cat: 'Lauk', unit: 'Kg', price: 36000 },
            '8991234567892': { name: 'Sayur Sawi & Capcay Mix', cat: 'Sayur', unit: 'Kg', price: 8000 },
            '8991234567893': { name: 'Minyak Goreng Sawit', cat: 'Lainnya', unit: 'Liter', price: 16500 }
        };
        const item = matched[this.barcodeScanned];
        if (item) {
            $store.toasts.add('Barcode terdeteksi: ' + item.name, 'success');
            // Populate form input tambah jika modal tambah kebuka
            document.getElementById('nama_bahan_add').value = item.name;
            document.getElementById('kategori_add').value = item.cat;
            document.getElementById('satuan_add').value = item.unit;
            document.getElementById('harga_add').value = item.price;
            this.modalBarcode = false;
        } else {
            $store.toasts.add('Barcode tidak terdaftar. Mendaftarkan barang baru.', 'warning');
        }
    }
}">

    <!-- WARNING BANNER EXPIRING H-7 & depleted STOCKS -->
    @if(isset($hampirKadaluarsaCount) && $hampirKadaluarsaCount > 0)
        <div class="mb-6 p-4 bg-amber-500/10 border border-amber-500/20 text-amber-400 rounded-2xl text-xs font-semibold flex items-center justify-between gap-3 animate-pulse">
            <div class="flex items-center gap-2">
                <span>⚠️</span>
                <span><strong>PERINGATAN GUDANG</strong>: Terdapat {{ $hampirKadaluarsaCount }} bahan makanan (Daging Ayam Fillet) yang memasuki masa kedaluwarsa H-7!</span>
            </div>
            <span class="text-[9px] font-mono bg-amber-500/20 px-2 py-1 rounded">Prioritas FIFO</span>
        </div>
    @endif

    <!-- HEADER & ACTIONS -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-black uppercase tracking-tight" :class="$store.theme.current === 'dark' ? 'text-white' : 'text-slate-900'">
                Logistik & Stok Gudang
            </h1>
            <p class="text-xs font-mono" :class="$store.theme.current === 'dark' ? 'text-slate-400' : 'text-slate-500'">
                Manajemen bahan pangan dapur utama untuk program Makan Bergizi Gratis (MBG)
            </p>
        </div>

        <div class="flex flex-wrap gap-2 w-full sm:w-auto">
            <!-- Scan Barcode Simulator -->
            <button @click="modalBarcode = true"
                    class="bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold font-mono px-3.5 py-2.5 rounded-xl border border-slate-700/50 flex items-center gap-2 cursor-pointer transition">
                <i data-lucide="qr-code" class="h-4 w-4"></i>
                SCAN BARCODE
            </button>

            <!-- Import Excel -->
            <form action="{{ route('dapur.inventaris.import') }}" method="POST" enctype="multipart/form-data" class="inline flex items-center gap-1.5 text-xs font-mono">
                @csrf
                <label class="bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700/50 px-3 py-2.5 rounded-xl cursor-pointer font-bold flex items-center gap-2 transition">
                    <i data-lucide="file-up" class="h-4 w-4"></i>
                    IMPORT EXCEL
                    <input type="file" name="excel_file" onchange="this.form.submit()" class="hidden">
                </label>
            </form>

            <!-- Add New -->
            <button @click="modalTambah = true" 
                    class="bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold font-mono px-4 py-2.5 rounded-xl shadow-lg transition cursor-pointer flex items-center gap-2">
                <i data-lucide="plus" class="h-4 w-4"></i>
                TAMBAH BAHAN
            </button>
        </div>
    </div>

    <!-- FILTER & SEARCH PANEL -->
    <div class="p-5 rounded-2xl mb-6 shadow-xl border"
         :class="$store.theme.current === 'dark' ? 'bg-slate-900 border-slate-800/80 text-white' : 'bg-white border-slate-200 text-slate-800'">
        
        <form action="{{ route('dapur.inventaris.index') }}" method="GET" class="flex flex-col md:flex-row gap-4 justify-between items-center font-mono text-xs">
            <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama bahan / kode barcode..." 
                       class="bg-slate-950/60 border border-slate-800/80 text-xs px-4 py-2.5 rounded-xl text-white focus:outline-none focus:border-emerald-500 w-full sm:w-64">
                
                <select name="kategori" class="bg-slate-950/60 border border-slate-800/80 text-xs px-3.5 py-2.5 rounded-xl text-slate-300 focus:outline-none focus:border-emerald-500 cursor-pointer">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ $kategoriFilter == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-2 w-full md:w-auto justify-end">
                <button type="submit" class="bg-emerald-600 hover:bg-emerald-500 text-white font-bold px-4 py-2.5 rounded-xl transition cursor-pointer">
                    CARI BAHAN
                </button>
                @if($search || $kategoriFilter)
                    <a href="{{ route('dapur.inventaris.index') }}" class="bg-rose-950 border border-rose-900/60 text-rose-400 font-bold px-4 py-2.5 rounded-xl transition flex items-center">
                        RESET
                    </a>
                @endif

                <!-- Export Actions -->
                <a href="{{ route('dapur.inventaris.export-excel') }}" class="bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700/50 px-3.5 py-2.5 rounded-xl flex items-center gap-1.5 transition">
                    <i data-lucide="download" class="h-4 w-4"></i> EXCEL
                </a>
                <a href="{{ route('dapur.inventaris.export-pdf') }}" target="_blank" class="bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700/50 px-3.5 py-2.5 rounded-xl flex items-center gap-1.5 transition">
                    <i data-lucide="printer" class="h-4 w-4"></i> PDF
                </a>
            </div>
        </form>
    </div>

    <!-- DATA TABLE REGISTER -->
    <div class="rounded-2xl border overflow-hidden shadow-xl"
         :class="$store.theme.current === 'dark' ? 'bg-slate-900 border-slate-800/80' : 'bg-white border-slate-200'">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b font-mono uppercase tracking-wider text-[10px]"
                        :class="$store.theme.current === 'dark' ? 'bg-slate-950 border-slate-800 text-slate-400' : 'bg-slate-100 border-slate-200 text-slate-500'">
                        <th class="p-4">Barcode</th>
                        <th class="p-4">Nama Bahan</th>
                        <th class="p-4">Kategori</th>
                        <th class="p-4 text-right">Stok Gudang</th>
                        <th class="p-4 text-right">Harga Satuan</th>
                        <th class="p-4">Expired Date</th>
                        <th class="p-4">Penyimpanan</th>
                        <th class="p-4 text-center">Status</th>
                        <th class="p-4 text-center">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y" :class="$store.theme.current === 'dark' ? 'divide-slate-800' : 'divide-slate-100'">
                    @forelse($bahanMakanan as $bahan)
                        <tr class="hover:bg-slate-500/5 transition">
                            <td class="p-4 font-mono font-bold text-emerald-400">{{ $bahan->barcode ?: 'N/A' }}</td>
                            <td class="p-4 font-bold">{{ $bahan->nama_bahan }}</td>
                            <td class="p-4 font-mono text-slate-400">
                                <span class="px-2.5 py-1 rounded-md border" :class="$store.theme.current === 'dark' ? 'bg-slate-950 border-slate-800' : 'bg-slate-50 border-slate-200'">{{ $bahan->kategori }}</span>
                            </td>
                            <td class="p-4 text-right font-mono font-bold text-blue-400">
                                {{ number_format($bahan->stok) }} <span class="text-[10px] text-slate-500 font-normal">{{ $bahan->satuan }}</span>
                            </td>
                            <td class="p-4 text-right font-mono font-bold text-slate-300">
                                Rp {{ number_format($bahan->harga) }}
                            </td>
                            <td class="p-4 font-mono text-slate-400">
                                {{ date('d M Y', strtotime($bahan->tanggal_kedaluwarsa)) }}
                            </td>
                            <td class="p-4 font-mono text-slate-400">{{ $bahan->lokasi_penyimpanan ?: 'Rak Umum' }}</td>
                            <td class="p-4 text-center">
                                @if($bahan->status_stok == 'Aman')
                                    <span class="text-[10px] font-bold font-mono px-2.5 py-1 rounded-lg bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">AMAN</span>
                                @elseif($bahan->status_stok == 'Menipis')
                                    <span class="text-[10px] font-bold font-mono px-2.5 py-1 rounded-lg bg-amber-500/10 text-amber-400 border border-amber-500/20">MENIPIS</span>
                                @else
                                    <span class="text-[10px] font-bold font-mono px-2.5 py-1 rounded-lg bg-rose-500/10 text-rose-400 border border-rose-500/20">HABIS</span>
                                @endif
                            </td>
                            <td class="p-4 text-center">
                                <div class="flex items-center justify-center gap-1 text-[10px] font-mono">
                                    <button @click="
                                        selectedBahan = {{ json_encode($bahan) }};
                                        modalEdit = true;
                                    " class="text-blue-400 hover:text-blue-300 bg-blue-500/5 px-2 py-1.5 rounded border border-blue-500/10 transition cursor-pointer">
                                        EDIT
                                    </button>
                                    
                                    <form action="{{ route('dapur.inventaris.destroy', $bahan->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus bahan ini dari gudang?')" class="inline">
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
                            <td colspan="9" class="p-8 text-center text-slate-500 font-mono">📭 Data bahan makanan di gudang kosong atau tidak ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL TAMBAH BAHAN -->
    <div x-show="modalTambah" class="fixed inset-0 bg-black/70 backdrop-blur-xs flex items-center justify-center p-4 z-50" x-transition>
        <div class="bg-slate-900 border border-slate-800 w-full max-w-md rounded-2xl overflow-hidden shadow-2xl p-6">
            <div class="flex justify-between items-center border-b border-slate-800 pb-3 mb-4">
                <h3 class="text-xs font-mono font-bold text-white uppercase tracking-wider">📦 Input Pasokan Bahan Masuk Dapur</h3>
                <button @click="modalTambah = false" class="text-slate-400 hover:text-white cursor-pointer text-lg">&times;</button>
            </div>
            
            <form action="{{ route('dapur.inventaris.store') }}" method="POST" class="space-y-4 font-mono text-xs text-slate-300" enctype="multipart/form-data">
                @csrf
                <div>
                    <label class="block text-slate-400 mb-1">Nama Bahan Makanan</label>
                    <input type="text" id="nama_bahan_add" name="nama_bahan" required placeholder="Contoh: Beras Pandan Wangi" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-white focus:outline-none focus:border-emerald-500">
                </div>
                
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-400 mb-1">Kategori</label>
                        <select id="kategori_add" name="kategori" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-emerald-500 cursor-pointer">
                            <option value="Beras">Beras</option>
                            <option value="Sayur">Sayur</option>
                            <option value="Lauk">Lauk</option>
                            <option value="Buah">Buah</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-slate-400 mb-1">Satuan</label>
                        <input type="text" id="satuan_add" name="satuan" required placeholder="Kg / Liter / Pcs" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-white focus:outline-none focus:border-emerald-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-400 mb-1">Stok Volume</label>
                        <input type="number" name="stok" required placeholder="100" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-white focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-slate-400 mb-1">Harga Satuan (Rp)</label>
                        <input type="number" id="harga_add" name="harga" required placeholder="14000" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-white focus:outline-none focus:border-emerald-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-400 mb-1">Tanggal Masuk</label>
                        <input type="date" name="tanggal_masuk" value="{{ date('Y-m-d') }}" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-slate-400 mb-1">Expired Date (FIFO)</label>
                        <input type="date" name="tanggal_kedaluwarsa" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-emerald-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-400 mb-1">Lokasi Rak</label>
                        <input type="text" name="lokasi_penyimpanan" placeholder="Cold Storage A" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-white focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-slate-400 mb-1">Barcode Manual (Optional)</label>
                        <input type="text" name="barcode" placeholder="899..." class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-white focus:outline-none focus:border-emerald-500">
                    </div>
                </div>

                <div>
                    <label class="block text-slate-400 mb-1">Foto Bahan</label>
                    <input type="file" name="foto_bahan" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-white focus:outline-none">
                </div>

                <div class="flex justify-end gap-2 pt-4 border-t border-slate-800 mt-6">
                    <button type="button" @click="modalTambah = false" class="bg-slate-950 hover:bg-slate-800 border border-slate-800 text-slate-400 px-4 py-2 rounded-xl cursor-pointer">BATAL</button>
                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-500 text-white px-4 py-2 rounded-xl font-bold cursor-pointer">SIMPAN GUDANG</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL EDIT BAHAN -->
    <div x-show="modalEdit" class="fixed inset-0 bg-black/70 backdrop-blur-xs flex items-center justify-center p-4 z-50" x-transition>
        <div class="bg-slate-900 border border-slate-800 w-full max-w-md rounded-2xl overflow-hidden shadow-2xl p-6">
            <div class="flex justify-between items-center border-b border-slate-800 pb-3 mb-4">
                <h3 class="text-xs font-mono font-bold text-white uppercase tracking-wider">✏️ Edit Informasi Logistik</h3>
                <button @click="modalEdit = false" class="text-slate-400 hover:text-white cursor-pointer text-lg">&times;</button>
            </div>
            
            <form :action="'{{ route('dapur.inventaris.update', '') }}/' + selectedBahan.id" method="POST" class="space-y-4 font-mono text-xs text-slate-300">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-slate-400 mb-1">Nama Bahan Makanan</label>
                    <input type="text" name="nama_bahan" required :value="selectedBahan.nama_bahan" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-white focus:outline-none focus:border-emerald-500">
                </div>
                
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-400 mb-1">Kategori</label>
                        <select name="kategori" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-emerald-500 cursor-pointer">
                            <option value="Beras" :selected="selectedBahan.kategori === 'Beras'">Beras</option>
                            <option value="Sayur" :selected="selectedBahan.kategori === 'Sayur'">Sayur</option>
                            <option value="Lauk" :selected="selectedBahan.kategori === 'Lauk'">Lauk</option>
                            <option value="Buah" :selected="selectedBahan.kategori === 'Buah'">Buah</option>
                            <option value="Lainnya" :selected="selectedBahan.kategori === 'Lainnya'">Lainnya</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-slate-400 mb-1">Satuan</label>
                        <input type="text" name="satuan" required :value="selectedBahan.satuan" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-white focus:outline-none focus:border-emerald-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-400 mb-1">Volume Stok</label>
                        <input type="number" name="stok" required :value="selectedBahan.stok" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-white focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-slate-400 mb-1">Harga Satuan (Rp)</label>
                        <input type="number" name="harga" required :value="selectedBahan.harga" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-white focus:outline-none focus:border-emerald-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-slate-400 mb-1">Tanggal Masuk</label>
                        <input type="date" name="tanggal_masuk" :value="selectedBahan.tanggal_masuk" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-slate-400 mb-1">Expired Date</label>
                        <input type="date" name="tanggal_kedaluwarsa" :value="selectedBahan.tanggal_kedaluwarsa" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-emerald-500">
                    </div>
                </div>

                <div>
                    <label class="block text-slate-400 mb-1">Lokasi Penyimpanan</label>
                    <input type="text" name="lokasi_penyimpanan" :value="selectedBahan.lokasi_penyimpanan" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-white focus:outline-none">
                </div>

                <div class="flex justify-end gap-2 pt-4 border-t border-slate-800 mt-6">
                    <button type="button" @click="modalEdit = false" class="bg-slate-950 hover:bg-slate-800 border border-slate-800 text-slate-400 px-4 py-2 rounded-xl cursor-pointer">BATAL</button>
                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-500 text-white px-4 py-2 rounded-xl font-bold cursor-pointer">UPDATE DATA</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL BARCODE SCANNER SIMULATOR -->
    <div x-show="modalBarcode" class="fixed inset-0 bg-black/75 backdrop-blur-sm flex items-center justify-center p-4 z-50" x-transition>
        <div class="bg-slate-900 border border-slate-800 w-full max-w-sm rounded-2xl overflow-hidden shadow-2xl p-6">
            <div class="flex justify-between items-center border-b border-slate-800 pb-3 mb-4">
                <h3 class="text-xs font-mono font-bold text-white uppercase tracking-wider">📷 Simulator Kamera Scanner Barcode</h3>
                <button @click="modalBarcode = false" class="text-slate-400 hover:text-white cursor-pointer text-lg">&times;</button>
            </div>
            
            <div class="bg-slate-950 p-6 rounded-xl border border-slate-800 flex flex-col items-center justify-center relative mb-4">
                <!-- Laser line scan effect -->
                <div class="absolute w-[80%] h-0.5 bg-rose-500 shadow-[0_0_8px_#f43f5e] animate-bounce top-5"></div>
                <div class="w-40 h-28 border border-slate-700/60 rounded flex items-center justify-center relative bg-slate-900/40">
                   <!-- Mock barcode icon -->
                   <svg xmlns="http://www.w3.org/2000/svg" width="60" height="40" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="1.5"><path d="M3 5v14M6 5v14M8 5v14M11 5v14M13 5v14M16 5v14M18 5v14M21 5v14"/></svg>
                </div>
                <span class="text-[9px] font-mono text-slate-500 mt-3 uppercase tracking-widest animate-pulse">Arahkan Kode ke Kamera</span>
            </div>

            <div class="space-y-4 font-mono text-xs">
                <div>
                    <label class="block text-slate-400 mb-1">Ketik Kode Barcode (Contoh: <span @click="barcodeScanned = '8991234567891'" class="text-emerald-400 cursor-pointer underline">8991234567891</span>)</label>
                    <input type="text" x-model="barcodeScanned" placeholder="Input kode barcode EAN..." 
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-white focus:outline-none focus:border-emerald-500 text-center font-bold tracking-wider">
                </div>

                <div class="flex gap-2">
                    <button type="button" @click="modalBarcode = false" class="flex-1 bg-slate-950 border border-slate-800 text-slate-400 py-2 rounded-xl cursor-pointer">TUTUP</button>
                    <button type="button" @click="checkBarcode()" class="flex-1 bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-2 rounded-xl cursor-pointer">SIMULASIKAN SCAN</button>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection