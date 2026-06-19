@extends('Layouts.app')

@section('content')
<div x-data="{ 
    modalTambah: false, 
    modalEdit: false, 
    selectedSchool: {}
}">

    <!-- HEADER -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-black uppercase tracking-tight" :class="$store.theme.current === 'dark' ? 'text-white' : 'text-slate-900'">
                Manajemen Sekolah Sasaran
            </h1>
            <p class="text-xs font-mono" :class="$store.theme.current === 'dark' ? 'text-slate-400' : 'text-slate-500'">
                Daftar lembaga sekolah penerima distribusi program Makan Bergizi Gratis (MBG)
            </p>
        </div>

        <button @click="modalTambah = true" 
                class="bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold font-mono px-4 py-2.5 rounded-xl shadow-lg transition cursor-pointer flex items-center gap-2">
            <i data-lucide="plus" class="h-4 w-4"></i>
            REGISTRASI SEKOLAH BARU
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
              <th class="p-4">Kode Sekolah</th>
              <th class="p-4">Nama Sekolah Sasaran</th>
              <th class="p-4">Kapasitas Siswa</th>
              <th class="p-4">Alamat Wilayah</th>
              <th class="p-4 text-center">Tindakan</th>
            </tr>
          </thead>
          <tbody class="divide-y" :class="$store.theme.current === 'dark' ? 'divide-slate-800' : 'divide-slate-100'">
            @foreach($schools as $sch)
              <tr class="hover:bg-slate-500/5 transition">
                <td class="p-4 font-mono font-bold text-emerald-400">{{ $sch->school_code }}</td>
                <td class="p-4 font-bold text-white">{{ $sch->name }}</td>
                <td class="p-4 font-mono font-bold text-blue-400">{{ $sch->total_students }} <span class="text-[10px] text-slate-500 font-normal">Siswa</span></td>
                <td class="p-4 text-slate-400 font-mono">{{ $sch->address }}</td>
                <td class="p-4 text-center">
                  <div class="flex items-center justify-center gap-1.5 text-[10px] font-mono">
                    <button @click="
                      selectedSchool = {{ json_encode($sch) }};
                      modalEdit = true;
                    " class="text-blue-400 hover:text-blue-300 bg-blue-500/5 px-2 py-1.5 rounded border border-blue-500/10 transition cursor-pointer">
                      EDIT
                    </button>
                    
                    <form action="{{ route('admin.schools.destroy', $sch->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus sekolah ini?')" class="inline">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="text-rose-500 hover:text-rose-400 bg-rose-500/5 px-2 py-1.5 rounded border border-rose-500/10 transition cursor-pointer">
                        HAPUS
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>

    <!-- MODAL TAMBAH -->
    <div x-show="modalTambah" class="fixed inset-0 bg-black/70 backdrop-blur-xs flex items-center justify-center p-4 z-50" x-transition>
      <div class="bg-slate-900 border border-slate-800 w-full max-w-md rounded-2xl overflow-hidden shadow-2xl p-6">
        <div class="flex justify-between items-center border-b border-slate-800 pb-3 mb-4">
          <h3 class="text-xs font-mono font-bold text-white uppercase tracking-wider">🏢 Registrasi Node Sekolah Baru</h3>
          <button @click="modalTambah = false" class="text-slate-400 hover:text-white cursor-pointer text-lg">&times;</button>
        </div>
        
        <form action="{{ route('admin.schools.store') }}" method="POST" class="space-y-4 font-mono text-xs text-slate-300">
          @csrf
          <div>
            <label class="block text-slate-400 mb-1">Kode Sekolah (Unique)</label>
            <input type="text" name="school_code" required placeholder="SCH005" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-white focus:outline-none focus:border-emerald-500">
          </div>
          <div>
            <label class="block text-slate-400 mb-1">Nama Sekolah</label>
            <input type="text" name="name" required placeholder="SDN 4 Lowokwaru" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-white focus:outline-none focus:border-emerald-500">
          </div>
          <div>
            <label class="block text-slate-400 mb-1">Kapasitas / Jumlah Siswa Terdaftar</label>
            <input type="number" name="total_students" required placeholder="150" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-white focus:outline-none focus:border-emerald-500">
          </div>
          <div>
            <label class="block text-slate-400 mb-1">Alamat Lengkap</label>
            <textarea name="address" required placeholder="Jl. Terusan Surabaya..." rows="3" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-white focus:outline-none focus:border-emerald-500"></textarea>
          </div>

          <div class="flex justify-end gap-2 pt-4 border-t border-slate-800 mt-6">
            <button type="button" @click="modalTambah = false" class="bg-slate-950 hover:bg-slate-800 border border-slate-800 text-slate-400 px-4 py-2 rounded-xl cursor-pointer">BATAL</button>
            <button type="submit" class="bg-emerald-600 hover:bg-emerald-500 text-white px-4 py-2 rounded-xl font-bold cursor-pointer">SIMPAN SEKOLAH</button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL EDIT -->
    <div x-show="modalEdit" class="fixed inset-0 bg-black/70 backdrop-blur-xs flex items-center justify-center p-4 z-50" x-transition>
      <div class="bg-slate-900 border border-slate-800 w-full max-w-md rounded-2xl overflow-hidden shadow-2xl p-6">
        <div class="flex justify-between items-center border-b border-slate-800 pb-3 mb-4">
          <h3 class="text-xs font-mono font-bold text-white uppercase tracking-wider">✏️ Edit Informasi Sekolah</h3>
          <button @click="modalEdit = false" class="text-slate-400 hover:text-white cursor-pointer text-lg">&times;</button>
        </div>
        
        <form :action="'{{ route('admin.schools.update', '') }}/' + selectedSchool.id" method="POST" class="space-y-4 font-mono text-xs text-slate-300">
          @csrf
          @method('PUT')
          <div>
            <label class="block text-slate-400 mb-1">Kode Sekolah</label>
            <input type="text" name="school_code" required :value="selectedSchool.school_code" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-white focus:outline-none focus:border-emerald-500">
          </div>
          <div>
            <label class="block text-slate-400 mb-1">Nama Sekolah</label>
            <input type="text" name="name" required :value="selectedSchool.name" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-white focus:outline-none focus:border-emerald-500">
          </div>
          <div>
            <label class="block text-slate-400 mb-1">Kapasitas / Jumlah Siswa</label>
            <input type="number" name="total_students" required :value="selectedSchool.total_students" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-white focus:outline-none focus:border-emerald-500">
          </div>
          <div>
            <label class="block text-slate-400 mb-1">Alamat Lengkap</label>
            <textarea name="address" required :value="selectedSchool.address" rows="3" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-white focus:outline-none focus:border-emerald-500"></textarea>
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
