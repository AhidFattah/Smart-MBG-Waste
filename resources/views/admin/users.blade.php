@extends('Layouts.app')

@section('content')
<div x-data="{ 
    modalTambah: false, 
    modalEdit: false, 
    selectedUser: {}
}">

    <!-- HEADER -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-black uppercase tracking-tight" :class="$store.theme.current === 'dark' ? 'text-white' : 'text-slate-900'">
                Manajemen Akun Pengguna
            </h1>
            <p class="text-xs font-mono" :class="$store.theme.current === 'dark' ? 'text-slate-400' : 'text-slate-500'">
                Daftar kredensial dan hak akses pengguna sistem MBG Smart Waste
            </p>
        </div>

        <button @click="modalTambah = true" 
                class="bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold font-mono px-4 py-2.5 rounded-xl shadow-lg transition cursor-pointer flex items-center gap-2">
            <i data-lucide="plus" class="h-4 w-4"></i>
            DAFTAR PENGGUNA BARU
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
              <th class="p-4">Nama User</th>
              <th class="p-4">Alamat Email</th>
              <th class="p-4">Peran (Role)</th>
              <th class="p-4">Sekolah Induk</th>
              <th class="p-4">Terdaftar</th>
              <th class="p-4 text-center">Tindakan</th>
            </tr>
          </thead>
          <tbody class="divide-y" :class="$store.theme.current === 'dark' ? 'divide-slate-800' : 'divide-slate-100'">
            @foreach($users as $user)
              <tr class="hover:bg-slate-500/5 transition">
                <td class="p-4 font-bold text-white flex items-center gap-2">
                  <div class="h-7 w-7 rounded bg-slate-800 flex items-center justify-center font-bold text-emerald-400 border border-slate-700/60">
                    {{ substr($user->name, 0, 1) }}
                  </div>
                  {{ $user->name }}
                </td>
                <td class="p-4 text-slate-300 font-mono">{{ $user->email }}</td>
                <td class="p-4 font-mono font-bold text-emerald-400">
                  <span class="px-2 py-0.5 rounded bg-slate-950 border border-slate-800 text-[10px]">
                    {{ $user->nama_role ?: strtoupper(str_replace('_', ' ', $user->role)) }}
                  </span>
                </td>
                <td class="p-4 text-slate-300 font-mono">{{ $user->nama_sekolah ?: 'Dapur Pusat' }}</td>
                <td class="p-4 font-mono text-slate-500">{{ date('d/m/Y', strtotime($user->created_at)) }}</td>
                <td class="p-4 text-center">
                  <div class="flex items-center justify-center gap-1.5 text-[10px] font-mono">
                    <button @click="
                      selectedUser = {{ json_encode($user) }};
                      modalEdit = true;
                    " class="text-blue-400 hover:text-blue-300 bg-blue-500/5 px-2 py-1.5 rounded border border-blue-500/10 transition cursor-pointer">
                      EDIT
                    </button>
                    
                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun ini?')" class="inline">
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
          <h3 class="text-xs font-mono font-bold text-white uppercase tracking-wider">👤 Buat Akun Pengguna Baru</h3>
          <button @click="modalTambah = false" class="text-slate-400 hover:text-white cursor-pointer text-lg">&times;</button>
        </div>
        
        <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-4 font-mono text-xs text-slate-300">
          @csrf
          <div>
            <label class="block text-slate-400 mb-1">Nama Lengkap</label>
            <input type="text" name="name" required placeholder="Ahmad Dani" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-white focus:outline-none focus:border-emerald-500">
          </div>
          <div>
            <label class="block text-slate-400 mb-1">Alamat Email</label>
            <input type="email" name="email" required placeholder="ahmad@email.com" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-white focus:outline-none focus:border-emerald-500">
          </div>
          <div>
            <label class="block text-slate-400 mb-1">Kata Sandi (Password)</label>
            <input type="password" name="password" required placeholder="Min. 6 Karakter" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-white focus:outline-none focus:border-emerald-500">
          </div>
          
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-slate-400 mb-1">Peran (Role)</label>
              <select name="role_id" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-emerald-500 cursor-pointer">
                @foreach($roles as $role)
                  <option value="{{ $role->id }}">{{ $role->display_name }}</option>
                @endforeach
              </select>
            </div>
            <div>
              <label class="block text-slate-400 mb-1">Sekolah Induk (Optional)</label>
              <select name="school_id" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-emerald-500 cursor-pointer">
                <option value="">-- Dapur Pusat / Tidak Ada --</option>
                @foreach($schools as $sch)
                  <option value="{{ $sch->id }}">{{ $sch->name }}</option>
                @endforeach
              </select>
            </div>
          </div>

          <div class="flex justify-end gap-2 pt-4 border-t border-slate-800 mt-6">
            <button type="button" @click="modalTambah = false" class="bg-slate-950 hover:bg-slate-800 border border-slate-800 text-slate-400 px-4 py-2 rounded-xl cursor-pointer">BATAL</button>
            <button type="submit" class="bg-emerald-600 hover:bg-emerald-500 text-white px-4 py-2 rounded-xl font-bold cursor-pointer">DAFTAR AKUN</button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL EDIT -->
    <div x-show="modalEdit" class="fixed inset-0 bg-black/70 backdrop-blur-xs flex items-center justify-center p-4 z-50" x-transition>
      <div class="bg-slate-900 border border-slate-800 w-full max-w-md rounded-2xl overflow-hidden shadow-2xl p-6">
        <div class="flex justify-between items-center border-b border-slate-800 pb-3 mb-4">
          <h3 class="text-xs font-mono font-bold text-white uppercase tracking-wider">✏️ Edit Profil Akun</h3>
          <button @click="modalEdit = false" class="text-slate-400 hover:text-white cursor-pointer text-lg">&times;</button>
        </div>
        
        <form :action="'{{ route('admin.users.update', '') }}/' + selectedUser.id" method="POST" class="space-y-4 font-mono text-xs text-slate-300">
          @csrf
          @method('PUT')
          <div>
            <label class="block text-slate-400 mb-1">Nama Lengkap</label>
            <input type="text" name="name" required :value="selectedUser.name" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-white focus:outline-none focus:border-emerald-500">
          </div>
          <div>
            <label class="block text-slate-400 mb-1">Alamat Email</label>
            <input type="email" name="email" required :value="selectedUser.email" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-white focus:outline-none focus:border-emerald-500">
          </div>
          <div>
            <label class="block text-slate-400 mb-1">Kata Sandi Baru (Kosongkan jika tetap)</label>
            <input type="password" name="password" placeholder="Masukkan password baru" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-white focus:outline-none focus:border-emerald-500">
          </div>
          
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-slate-400 mb-1">Peran (Role)</label>
              <select name="role_id" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-emerald-500 cursor-pointer">
                @foreach($roles as $role)
                  <option value="{{ $role->id }}" :selected="selectedUser.role_id == {{ $role->id }}">{{ $role->display_name }}</option>
                @endforeach
              </select>
            </div>
            <div>
              <label class="block text-slate-400 mb-1">Sekolah Induk</label>
              <select name="school_id" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-white focus:outline-none cursor-pointer">
                <option value="">-- Dapur Pusat / Tidak Ada --</option>
                @foreach($schools as $sch)
                  <option value="{{ $sch->id }}" :selected="selectedUser.school_id == {{ $sch->id }}">{{ $sch->name }}</option>
                @endforeach
              </select>
            </div>
          </div>

          <div class="flex justify-end gap-2 pt-4 border-t border-slate-800 mt-6">
            <button type="button" @click="modalEdit = false" class="bg-slate-950 hover:bg-slate-800 border border-slate-800 text-slate-400 px-4 py-2 rounded-xl cursor-pointer">BATAL</button>
            <button type="submit" class="bg-emerald-600 hover:bg-emerald-500 text-white px-4 py-2 rounded-xl font-bold cursor-pointer">UPDATE AKUN</button>
          </div>
        </form>
      </div>
    </div>

</div>
@endsection
