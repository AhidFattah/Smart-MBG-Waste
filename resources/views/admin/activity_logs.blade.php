@extends('Layouts.app')

@section('content')
<div>
    <!-- HEADER -->
    <div class="mb-8">
        <h1 class="text-2xl font-black uppercase tracking-tight" :class="$store.theme.current === 'dark' ? 'text-white' : 'text-slate-900'">
            Audit Log Aktivitas Pengguna
        </h1>
        <p class="text-xs font-mono" :class="$store.theme.current === 'dark' ? 'text-slate-400' : 'text-slate-500'">
            Log riwayat audit sistem merekam seluruh aktivitas keamanan, perubahan data gudang, dan distribusi
        </p>
    </div>

    <!-- DATA TABLE -->
    <div class="rounded-2xl border overflow-hidden shadow-xl mb-6"
         :class="$store.theme.current === 'dark' ? 'bg-slate-900 border-slate-800/80' : 'bg-white border-slate-200'">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b font-mono uppercase tracking-wider text-[10px]"
                        :class="$store.theme.current === 'dark' ? 'bg-slate-950 border-slate-800 text-slate-400' : 'bg-slate-100 border-slate-200 text-slate-500'">
                        <th class="p-4">Tanggal & Waktu</th>
                        <th class="p-4">Nama Pengguna</th>
                        <th class="p-4">Aktivitas</th>
                        <th class="p-4">Keterangan / Deskripsi Audit</th>
                        <th class="p-4">IP Address</th>
                        <th class="p-4">Browser / User Agent</th>
                    </tr>
                </thead>
                <tbody class="divide-y" :class="$store.theme.current === 'dark' ? 'divide-slate-800' : 'divide-slate-100'">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-500/5 transition font-mono">
                            <td class="p-4 text-slate-400 whitespace-nowrap">{{ date('d M Y H:i:s', strtotime($log->created_at)) }}</td>
                            <td class="p-4 font-bold text-white whitespace-nowrap">
                                @if($log->nama_user)
                                    {{ $log->nama_user }}
                                    <p class="text-[9px] text-slate-500 font-normal mt-0.5">{{ $log->email_user }}</p>
                                @else
                                    <span class="text-slate-500">Sistem Anonim</span>
                                @endif
                            </td>
                            <td class="p-4">
                                <span class="px-2 py-0.5 rounded font-bold text-[9px] border"
                                      :class="'{{ $log->aktivitas }}' === 'LOGIN_SUCCESS' || '{{ $log->aktivitas }}' === 'RESTORE_DATABASE' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : ('{{ $log->aktivitas }}' === 'LOGIN_FAILED' || '{{ $log->aktivitas }}' === 'DELETE_USER' ? 'bg-rose-500/10 text-rose-400 border-rose-500/20' : 'bg-blue-500/10 text-blue-400 border-blue-500/20')">
                                    {{ $log->aktivitas }}
                                </span>
                            </td>
                            <td class="p-4 text-slate-300 leading-normal max-w-sm truncate" :title="'{{ $log->deskripsi }}'">
                                {{ $log->deskripsi }}
                            </td>
                            <td class="p-4 text-slate-400 font-bold">{{ $log->ip_address }}</td>
                            <td class="p-4 text-slate-500 max-w-xs truncate" :title="'{{ $log->user_agent }}'">{{ $log->user_agent }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-500 font-mono">📭 Belum ada riwayat aktivitas pengguna tercatat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- PAGINATION -->
    <div class="mt-4">
        {{ $logs->links() }}
    </div>

</div>
@endsection
