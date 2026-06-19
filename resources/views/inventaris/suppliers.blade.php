@extends('Layouts.app')

@section('content')
<div x-data="{ 
    modalTambah: false, 
    modalEdit: false, 
    selectedSupplier: {}
}">

    <!-- HEADER -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-black uppercase tracking-tight" :class="$store.theme.current === 'dark' ? 'text-white' : 'text-slate-900'">
                Manajemen Supplier Bahan Makanan
            </h1>
            <p class="text-xs font-mono" :class="$store.theme.current === 'dark' ? 'text-slate-400' : 'text-slate-500'">
                Kelola daftar mitra koperasi dan distributor pemasok bahan pangan bergizi
            </p>
        </div>

        <button @click="modalTambah = true" 
                class="bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold font-mono px-4 py-2.5 rounded-xl shadow-lg transition cursor-pointer flex items-center gap-2">
            <i data-lucide="plus" class="h-4 w-4"></i>
            TAMBAH SUPPLIER
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
                        <th class="p-4">ID</th>
                        <th class="p-4">Nama Supplier</th>
                        <th class="p-4">Nama Kontak</th>
                        <th class="p-4">Nomor HP / Telepon</th>
                        <th class="p-4">Alamat Kantor / Kebun</th>
                        <th class="p-4 text-center">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y" :class="$store.theme.current === 'dark' ? 'divide-slate-800' : 'divide-slate-100'">
                    @forelse($suppliers as $supplier)
                        <tr class="hover:bg-slate-500/5 transition">
                            <td class="p-4 font-mono text-slate-400">#{{ $supplier->id }}</td>
                            <td class="p-4 font-bold text-white">{{ $supplier->name }}</td>
                            <td class="p-4 text-slate-300 font-mono">{{ $supplier->contact_name ?: '-' }}</td>
                            <td class="p-4 text-slate-300 font-mono">{{ $supplier->phone ?: '-' }}</td>
                            <td class="p-4 text-slate-400 font-mono">{{ $supplier->address ?: '-' }}</td>
                            <td class="p-4 text-center">
                                <div class="flex items-center justify-center gap-1 text-[10px] font-mono">
                                    <button @click="
                                        selectedSupplier = {{ json_encode($supplier) }};
                                        modalEdit = true;
                                    " class="text-blue-400 hover:text-blue-300 bg-blue-500/5 px-2 py-1.5 rounded border border-blue-500/10 transition cursor-pointer">
                                        EDIT
                                    </button>
                                    
                                    <form action="{{ route('dapur.suppliers.destroy', $supplier->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus supplier ini?')" class="inline">
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
                            <td colspan="6" class="p-8 text-center text-slate-500 font-mono">📭 Belum ada daftar supplier logistik terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL TAMBAH -->
    <div x-show="modalTambah" class="fixed inset-0 bg-black/70 backdrop-blur-xs flex items-center justify-center p-4 z-50" x-transition>
        <div class="bg-slate-900 border border-slate-800 w-full max-w-md rounded-2xl overflow-hidden shadow-2xl p-6">
            <div class="flex justify-between items-center border-b border-slate-800 pb-3 mb-4">
                <h3 class="text-xs font-mono font-bold text-white uppercase tracking-wider">🏢 Daftarkan Supplier Baru</h3>
                <button @click="modalTambah = false" class="text-slate-400 hover:text-white cursor-pointer text-lg">&times;</button>
            </div>
            
            <form action="{{ route('dapur.suppliers.store') }}" method="POST" class="space-y-4 font-mono text-xs text-slate-300">
                @csrf
                <div>
                    <label class="block text-slate-400 mb-1">Nama Supplier / Koperasi</label>
                    <input type="text" name="name" required placeholder="Contoh: Koperasi Tani Makmur" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-white focus:outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-slate-400 mb-1">Nama Narahubung (Contact Person)</label>
                    <input type="text" name="contact_name" placeholder="Pak Joko" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-white focus:outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-slate-400 mb-1">Nomor Telepon</label>
                    <input type="text" name="phone" placeholder="081234..." class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-white focus:outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-slate-400 mb-1">Alamat Kantor / Kebun</label>
                    <textarea name="address" placeholder="Jl. Raya Malang..." rows="3" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-white focus:outline-none focus:border-emerald-500"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-4 border-t border-slate-800 mt-6">
                    <button type="button" @click="modalTambah = false" class="bg-slate-950 hover:bg-slate-800 border border-slate-800 text-slate-400 px-4 py-2 rounded-xl cursor-pointer">BATAL</button>
                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-500 text-white px-4 py-2 rounded-xl font-bold cursor-pointer">SIMPAN MITRA</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL EDIT -->
    <div x-show="modalEdit" class="fixed inset-0 bg-black/70 backdrop-blur-xs flex items-center justify-center p-4 z-50" x-transition>
        <div class="bg-slate-900 border border-slate-800 w-full max-w-md rounded-2xl overflow-hidden shadow-2xl p-6">
            <div class="flex justify-between items-center border-b border-slate-800 pb-3 mb-4">
                <h3 class="text-xs font-mono font-bold text-white uppercase tracking-wider">✏️ Edit Profil Supplier</h3>
                <button @click="modalEdit = false" class="text-slate-400 hover:text-white cursor-pointer text-lg">&times;</button>
            </div>
            
            <form :action="'{{ route('dapur.suppliers.update', '') }}/' + selectedSupplier.id" method="POST" class="space-y-4 font-mono text-xs text-slate-300">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-slate-400 mb-1">Nama Supplier / Koperasi</label>
                    <input type="text" name="name" required :value="selectedSupplier.name" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-white focus:outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-slate-400 mb-1">Nama Narahubung (Contact Person)</label>
                    <input type="text" name="contact_name" :value="selectedSupplier.contact_name" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-white focus:outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-slate-400 mb-1">Nomor Telepon</label>
                    <input type="text" name="phone" :value="selectedSupplier.phone" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-white focus:outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-slate-400 mb-1">Alamat Kantor / Kebun</label>
                    <textarea name="address" :value="selectedSupplier.address" rows="3" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-white focus:outline-none focus:border-emerald-500"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-4 border-t border-slate-800 mt-6">
                    <button type="button" @click="modalEdit = false" class="bg-slate-950 hover:bg-slate-800 border border-slate-800 text-slate-400 px-4 py-2 rounded-xl cursor-pointer">BATAL</button>
                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-500 text-white px-4 py-2 rounded-xl font-bold cursor-pointer">UPDATE DATA</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
