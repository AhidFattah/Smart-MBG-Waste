<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Laporan Food Waste - Smart MBG</title>
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

  <div class="max-w-4xl mx-auto mb-6 flex justify-between items-center no-print">
    <button onclick="window.history.back()" class="text-xs bg-slate-800 text-slate-300 px-4 py-2 rounded-xl border border-slate-700/50 hover:bg-slate-700 transition cursor-pointer">
      &larr; KEMBALI
    </button>
    <button onclick="window.print()" class="text-xs bg-emerald-600 hover:bg-emerald-500 text-white font-bold px-4 py-2 rounded-xl shadow-lg transition cursor-pointer">
      🖨️ CETAK LAPORAN
    </button>
  </div>

  <div class="max-w-4xl mx-auto bg-white border border-slate-200 p-8 rounded-2xl shadow-xl text-black">
    
    <!-- HEADER -->
    <div class="flex justify-between items-start border-b-2 border-slate-900 pb-6 mb-6">
      <div>
        <h1 class="text-xl font-black uppercase text-emerald-600">Smart MBG Waste & Inventory</h1>
        <p class="text-[9px] font-mono text-slate-500">Program Makan Bergizi Gratis Nasional</p>
        <p class="text-[9px] font-mono text-slate-500">Laporan Rekapitulasi Sisa Konsumsi Hilir (Food Waste)</p>
      </div>
      <div class="text-right">
        <h2 class="text-sm font-black text-slate-800">DOKUMEN MONITORING LAPORAN</h2>
        <p class="text-[9px] font-mono text-slate-400 mt-1">Dicetak pada: {{ date('d F Y H:i') }}</p>
        @if($dateStart && $dateEnd)
          <p class="text-[9px] font-mono text-slate-500">Periode: {{ date('d M Y', strtotime($dateStart)) }} s/d {{ date('d M Y', strtotime($dateEnd)) }}</p>
        @endif
      </div>
    </div>

    <!-- TABLE -->
    <table class="w-full text-left border-collapse text-[10px] mb-12">
      <thead>
        <tr class="border-b-2 border-slate-800 bg-slate-50 font-bold font-mono">
          <th class="p-2.5">Tanggal</th>
          <th class="p-2.5">Sekolah Sasaran</th>
          <th class="p-2.5">Menu Makanan</th>
          <th class="p-2.5 text-center">Porsi Kirim</th>
          <th class="p-2.5 text-right">Berat Sisa (Kg)</th>
          <th class="p-2.5 text-center">Porsi Sisa</th>
          <th class="p-2.5 text-center">Indeks Waste (%)</th>
          <th class="p-2.5 text-center">Status Koreksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($laporanData as $row)
          <tr class="border-b">
            <td class="p-2.5 font-mono">{{ date('d/m/Y', strtotime($row->tanggal_distribusi)) }}</td>
            <td class="p-2.5 font-bold">{{ $row->nama_sekolah }}</td>
            <td class="p-2.5 text-slate-700 font-mono">{{ $row->nama_menu }}</td>
            <td class="p-2.5 text-center font-mono font-bold">{{ $row->total_porsi_dikirim }} Pcs</td>
            <td class="p-2.5 text-right font-mono font-bold">{{ $row->berat_sisa_makanan }} Kg</td>
            <td class="p-2.5 text-center font-mono">{{ $row->jumlah_sisa_porsi }} Pcs</td>
            <td class="p-2.5 text-center font-mono font-bold">{{ $row->persentase_waste }}%</td>
            <td class="p-2.5 text-center">
              <span class="font-mono font-bold text-[9px] px-2 py-0.5 rounded border" 
                    class="{{ $row->kategori_waste == 'Tinggi' ? 'text-rose-500 border-rose-500/30' : 'text-emerald-500 border-emerald-500/30' }}">
                {{ $row->kategori_waste == 'Tinggi' ? 'REDUKSI KUOTA' : 'OPTIMAL' }}
              </span>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="8" class="p-6 text-center text-slate-500 font-mono">Tidak ada data tercatat.</td>
          </tr>
        @endforelse
      </tbody>
    </table>

    <!-- SIGNATURES -->
    <div class="flex justify-end text-center text-[10px] font-mono mt-12">
      <div class="w-64">
        <p class="text-slate-500">Mengesahkan, Kepala Pusat MBG,</p>
        <div class="h-16"></div>
        <p class="font-bold underline text-slate-800">Ahmad Super Admin</p>
        <p class="text-[9px] text-slate-400">NIP. 19890812 201504 1 002</p>
      </div>
    </div>

  </div>

  <script>
    // window.addEventListener('load', () => window.print());
  </script>
</body>
</html>
