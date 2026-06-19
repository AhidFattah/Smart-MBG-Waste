<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Surat Jalan #{{ $distribution->id }} - Smart MBG</title>
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Outfit', sans-serif;
    }
    @media print {
      .no-print {
        display: none !important;
      }
      body {
        background: white;
        color: black;
      }
    }
  </style>
</head>
<body class="bg-slate-100 text-slate-900 p-6 md:p-12">

  <!-- Floating Print Buttons (hidden during print) -->
  <div class="max-w-3xl mx-auto mb-6 flex justify-between items-center no-print">
    <a href="{{ route('dapur.distributions.index') }}" class="text-xs bg-slate-800 text-slate-300 px-4 py-2 rounded-xl border border-slate-700/50 hover:bg-slate-700 transition">
      &larr; KEMBALI KEBUTUHAN
    </a>
    <button onclick="window.print()" class="text-xs bg-emerald-600 hover:bg-emerald-500 text-white font-bold px-4 py-2 rounded-xl shadow-lg transition cursor-pointer">
      🖨️ CETAK DOKUMEN
    </button>
  </div>

  <!-- SURAT JALAN CARD -->
  <div class="max-w-3xl mx-auto bg-white border border-slate-200 p-8 rounded-2xl shadow-xl text-black">
    
    <!-- HEADER -->
    <div class="flex justify-between items-start border-b-2 border-slate-900 pb-6 mb-6">
      <div>
        <h1 class="text-2xl font-black uppercase tracking-tight text-emerald-600">SMART MBG DAPUR PUSAT</h1>
        <p class="text-[10px] font-mono text-slate-500 mt-0.5">Jl. Simpang Industri No. 4, Lowokwaru, Kota Malang</p>
        <p class="text-[10px] font-mono text-slate-500">Telp: (0341) 555-889 | Email: dapur@mbg.com</p>
      </div>
      <div class="text-right">
        <h2 class="text-lg font-black tracking-wider text-slate-800">SURAT JALAN DOKUMEN</h2>
        <p class="text-xs font-mono font-bold text-slate-500 mt-1">NO: #{{ $distribution->qr_code_surat_jalan }}</p>
        <p class="text-[10px] font-mono text-slate-400">Tanggal: {{ date('d F Y', strtotime($distribution->tanggal_distribusi)) }}</p>
      </div>
    </div>

    <!-- DETAIL TIKET -->
    <div class="grid grid-cols-2 gap-6 mb-8 text-xs leading-relaxed">
      <div>
        <p class="text-[10px] font-mono text-slate-500 uppercase">Tujuan Pengiriman:</p>
        <p class="font-bold text-slate-800 mt-0.5">{{ $distribution->nama_sekolah }}</p>
        <p class="text-slate-600">{{ $distribution->alamat_sekolah }}</p>
        <p class="text-slate-500 font-mono mt-1">Kapasitas: {{ $distribution->kapasitas_sekolah }} Siswa</p>
      </div>
      <div class="border-l pl-6">
        <p class="text-[10px] font-mono text-slate-500 uppercase">Ekspedisi Logistik:</p>
        <p class="font-bold text-slate-800 mt-0.5">Driver: {{ $distribution->petugas_distribusi ?: 'Kurir Kargo A' }}</p>
        <p class="text-slate-600">Kendaraan: {{ $distribution->kendaraan_distribusi ?: 'Mobil Box (N 1234 AB)' }}</p>
        <p class="text-slate-500 font-mono mt-1">Waktu berangkat: {{ $distribution->waktu_distribusi ?: '08:30 WIB' }}</p>
      </div>
    </div>

    <!-- TABEL MANIFES ITEM -->
    <table class="w-full text-left border-collapse text-xs mb-8">
      <thead>
        <tr class="border-b-2 border-slate-800 bg-slate-50">
          <th class="p-3">Item Menu MBG</th>
          <th class="p-3 text-center">Siswa Hadir</th>
          <th class="p-3 text-center">Cadangan (3%)</th>
          <th class="p-3 text-right">Total Kargo Kirim</th>
        </tr>
      </thead>
      <tbody>
        <tr class="border-b">
          <td class="p-3">
            <p class="font-bold text-slate-800">{{ $distribution->nama_menu }}</p>
            <p class="text-[10px] text-slate-500 font-mono mt-0.5">Rotasi Menu Nutrisi Dapur Utama</p>
          </td>
          <td class="p-3 text-center font-mono font-bold">{{ $distribution->jumlah_siswa_hadir ?: $distribution->total_porsi_dikirim }} anak</td>
          <td class="p-3 text-center font-mono text-slate-500">{{ $distribution->persentase_cadangan }}%</td>
          <td class="p-3 text-right font-mono font-bold text-lg text-emerald-600">{{ $distribution->total_porsi_dikirim }} Box</td>
        </tr>
      </tbody>
    </table>

    <!-- QR CODE & SIGNATURES -->
    <div class="flex flex-col sm:flex-row justify-between items-center gap-6 mt-12 pt-6 border-t">
      <!-- QR manifest visualizer -->
      <div class="text-center font-mono">
        <div class="bg-white p-3 border-2 border-slate-900 inline-block mb-1 shadow-sm">
          <div class="w-24 h-24 border-4 border-slate-900 p-1 flex flex-col justify-between items-center relative">
            <div class="w-5 h-5 border-2 border-slate-900 absolute top-0 left-0 bg-slate-900"></div>
            <div class="w-5 h-5 border-2 border-slate-900 absolute top-0 right-0 bg-slate-900"></div>
            <div class="w-5 h-5 border-2 border-slate-900 absolute bottom-0 left-0 bg-slate-900"></div>
            <div class="text-[6px] font-black text-slate-900 mt-8">SMART MBG</div>
            <div class="text-[5px] text-slate-500" x-text="'#{{ $distribution->qr_code_surat_jalan }}'"></div>
          </div>
        </div>
        <p class="text-[9px] text-slate-500 uppercase">Manifest QR-Scan Tanda Terima</p>
      </div>

      <!-- Tanda tangan -->
      <div class="flex gap-12 text-center text-[10px] font-mono">
        <div>
          <p class="text-slate-500">Pengirim (Dapur Utama),</p>
          <div class="h-16"></div>
          <p class="font-bold underline text-slate-800">{{ Auth::user()->name }}</p>
          <p class="text-[9px] text-slate-400">Dapur Pusat Malang</p>
        </div>
        <div>
          <p class="text-slate-500">Kurir Pengantar,</p>
          <div class="h-16"></div>
          <p class="font-bold underline text-slate-800">{{ $distribution->petugas_distribusi ?: 'Driver Kargo' }}</p>
          <p class="text-[9px] text-slate-400">Logistik Kargo</p>
        </div>
        <div>
          <p class="text-slate-500">Penerima (Sekolah),</p>
          <div class="h-16"></div>
          <p class="font-bold underline text-slate-800">{{ $distribution->penerima_nama ?: '........................' }}</p>
          <p class="text-[9px] text-slate-400">NIP/Petugas Sekolah</p>
        </div>
      </div>
    </div>

  </div>

  <script>
    // Otomatis men-trigger print dialog pada saat halaman terbuka
    window.addEventListener('load', () => {
      // setTimeout(() => window.print(), 1000);
    });
  </script>
</body>
</html>
