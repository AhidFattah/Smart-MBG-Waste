<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\ActivityLogger;
use App\Models\Inventory;
use App\Models\Supplier;
use App\Models\InventoryLog;
use Carbon\Carbon;

class InventarisController extends Controller
{
    // A. READ & LIST INVENTARIS GUDANG
    public function index(Request $request)
    {
        $search = $request->input('search');
        $kategoriFilter = $request->input('kategori');

        $query = DB::table('inventories')
            ->leftJoin('suppliers', 'inventories.supplier_id', '=', 'suppliers.id')
            ->select('inventories.*', 'suppliers.name as nama_supplier');

        if ($search) {
            $query->where('inventories.nama_bahan', 'like', '%' . $search . '%')
                  ->orWhere('inventories.barcode', 'like', '%' . $search . '%');
        }

        if ($kategoriFilter) {
            $query->where('inventories.kategori', $kategoriFilter);
        }

        $bahanMakanan = $query->orderBy('inventories.tanggal_kedaluwarsa', 'asc')->get();
        $categories = ['Beras', 'Sayur', 'Lauk', 'Buah', 'Lainnya'];
        $suppliers = DB::table('suppliers')->get();

        // Cek stok menipis / hampir kadaluarsa untuk notifikasi real-time
        $hampirKadaluarsaCount = 0;
        foreach ($bahanMakanan as $bahan) {
            $diffInDays = Carbon::now()->diffInDays(Carbon::parse($bahan->tanggal_kedaluwarsa), false);
            if ($diffInDays >= 0 && $diffInDays <= 7) {
                $hampirKadaluarsaCount++;
            }
        }

        return view('inventaris.index', compact('bahanMakanan', 'categories', 'suppliers', 'search', 'kategoriFilter', 'hampirKadaluarsaCount'));
    }

    // B. CREATE INVENTARIS BARU (FIFO BATCH MASUK)
    public function store(Request $request)
    {
        $request->validate([
            'nama_bahan' => 'required|string',
            'kategori' => 'required|string',
            'stok' => 'required|integer|min:0',
            'satuan' => 'required|string',
            'tanggal_masuk' => 'required|date',
            'tanggal_kedaluwarsa' => 'required|date',
            'harga' => 'required|numeric',
            'supplier_id' => 'nullable|integer',
            'lokasi_penyimpanan' => 'nullable|string',
        ]);

        $stok = (int) $request->input('stok');
        $status_stok = 'Aman';
        if ($stok == 0) {
            $status_stok = 'Habis';
        } elseif ($stok < 20) {
            $status_stok = 'Menipis';
        }

        // Mock Foto Upload
        $foto_bahan = null;
        if ($request->hasFile('foto_bahan')) {
            $file = $request->file('foto_bahan');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/inventoris'), $filename);
            $foto_bahan = 'uploads/inventoris/' . $filename;
        }

        // Generate Barcode / QR
        $barcode = $request->input('barcode') ?: '899' . rand(1000000000, 9999999999);
        $qr_code = 'INV-' . strtoupper($request->input('kategori')) . '-' . rand(100, 999);

        $inventoryId = DB::table('inventories')->insertGetId([
            'nama_bahan' => $request->input('nama_bahan'),
            'kategori' => $request->input('kategori'),
            'stok' => $stok,
            'satuan' => $request->input('satuan'),
            'tanggal_masuk' => $request->input('tanggal_masuk'),
            'tanggal_kedaluwarsa' => $request->input('tanggal_kedaluwarsa'),
            'status_stok' => $status_stok,
            'supplier_id' => $request->input('supplier_id'),
            'harga' => $request->input('harga'),
            'lokasi_penyimpanan' => $request->input('lokasi_penyimpanan'),
            'barcode' => $barcode,
            'qr_code' => $qr_code,
            'foto_bahan' => $foto_bahan,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Catat ke log transaksi (FIFO - Masuk)
        DB::table('inventory_logs')->insert([
            'inventory_id' => $inventoryId,
            'tipe' => 'masuk',
            'jumlah' => $stok,
            'sisa_stok_saat_itu' => $stok,
            'harga_satuan' => $request->input('harga'),
            'keterangan' => 'Stok masuk awal dari form CRUD',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        ActivityLogger::log('CREATE_INVENTORY', 'Menambahkan bahan makanan: ' . $request->input('nama_bahan') . ' sejumlah ' . $stok . ' ' . $request->input('satuan'));

        // Cek apakah langsung menipis/habis untuk trigger notifications
        if ($stok < 20) {
            DB::table('notifications')->insert([
                'tipe' => $stok == 0 ? 'danger' : 'warning',
                'message' => ($stok == 0 ? '🚨 STOK HABIS: ' : '⚠️ STOK MENIPIS: ') . $request->input('nama_bahan') . ' tersisa ' . $stok . ' ' . $request->input('satuan'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return redirect()->back()->with('success', 'Bahan logistik baru berhasil disimpan ke gudang!');
    }

    // C. UPDATE DETAIL BAHAN
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_bahan' => 'required|string',
            'kategori' => 'required|string',
            'stok' => 'required|integer|min:0',
            'satuan' => 'required|string',
            'tanggal_masuk' => 'required|date',
            'tanggal_kedaluwarsa' => 'required|date',
            'harga' => 'required|numeric',
            'supplier_id' => 'nullable|integer',
            'lokasi_penyimpanan' => 'nullable|string',
        ]);

        $stokBaru = (int) $request->input('stok');
        $status_stok = 'Aman';
        if ($stokBaru == 0) {
            $status_stok = 'Habis';
        } elseif ($stokBaru < 20) {
            $status_stok = 'Menipis';
        }

        $bahanLama = DB::table('inventories')->where('id', $id)->first();
        $stokSelisih = $stokBaru - $bahanLama->stok;

        DB::table('inventories')->where('id', $id)->update([
            'nama_bahan' => $request->input('nama_bahan'),
            'kategori' => $request->input('kategori'),
            'stok' => $stokBaru,
            'satuan' => $request->input('satuan'),
            'tanggal_masuk' => $request->input('tanggal_masuk'),
            'tanggal_kedaluwarsa' => $request->input('tanggal_kedaluwarsa'),
            'status_stok' => $status_stok,
            'supplier_id' => $request->input('supplier_id'),
            'harga' => $request->input('harga'),
            'lokasi_penyimpanan' => $request->input('lokasi_penyimpanan'),
            'updated_at' => now(),
        ]);

        // Catat log transaksi masuk/keluar berdasarkan selisih stok
        if ($stokSelisih != 0) {
            DB::table('inventory_logs')->insert([
                'inventory_id' => $id,
                'tipe' => $stokSelisih > 0 ? 'masuk' : 'keluar',
                'jumlah' => abs($stokSelisih),
                'sisa_stok_saat_itu' => $stokBaru,
                'harga_satuan' => $request->input('harga'),
                'keterangan' => 'Koreksi stok manual melalui edit detail',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        ActivityLogger::log('UPDATE_INVENTORY', 'Memperbarui detail bahan makanan: ' . $request->input('nama_bahan'));

        return redirect()->back()->with('success', 'Data bahan makanan berhasil diperbarui!');
    }

    // D. DELETE BAHAN
    public function destroy($id)
    {
        $bahan = DB::table('inventories')->where('id', $id)->first();
        if ($bahan) {
            ActivityLogger::log('DELETE_INVENTORY', 'Menghapus bahan makanan: ' . $bahan->nama_bahan);
            DB::table('inventories')->where('id', $id)->delete();
            return redirect()->back()->with('success', 'Bahan logistik berhasil dihapus dari gudang.');
        }
        return redirect()->back()->with('error', 'Bahan tidak ditemukan.');
    }

    // ==========================================
    // MODUL CRUD SUPPLIERS
    // ==========================================
    public function indexSuppliers(Request $request)
    {
        $suppliers = DB::table('suppliers')->get();
        return view('inventaris.suppliers', compact('suppliers'));
    }

    public function storeSupplier(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'contact_name' => 'nullable|string',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
        ]);

        DB::table('suppliers')->insert([
            'name' => $request->input('name'),
            'contact_name' => $request->input('contact_name'),
            'phone' => $request->input('phone'),
            'address' => $request->input('address'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        ActivityLogger::log('CREATE_SUPPLIER', 'Menambahkan supplier baru: ' . $request->input('name'));
        return redirect()->back()->with('success', 'Supplier baru berhasil ditambahkan!');
    }

    public function updateSupplier(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string',
            'contact_name' => 'nullable|string',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
        ]);

        DB::table('suppliers')->where('id', $id)->update([
            'name' => $request->input('name'),
            'contact_name' => $request->input('contact_name'),
            'phone' => $request->input('phone'),
            'address' => $request->input('address'),
            'updated_at' => now(),
        ]);

        ActivityLogger::log('UPDATE_SUPPLIER', 'Memperbarui supplier: ' . $request->input('name'));
        return redirect()->back()->with('success', 'Data supplier berhasil diperbarui!');
    }

    public function destroySupplier($id)
    {
        $supplier = DB::table('suppliers')->where('id', $id)->first();
        if ($supplier) {
            ActivityLogger::log('DELETE_SUPPLIER', 'Menghapus supplier: ' . $supplier->name);
            DB::table('suppliers')->where('id', $id)->delete();
            return redirect()->back()->with('success', 'Supplier berhasil dihapus.');
        }
        return redirect()->back()->with('error', 'Supplier tidak ditemukan.');
    }

    // ==========================================
    // EXCEL / CSV IMPORT SIMULATOR & EXPORTS
    // ==========================================
    public function importExcel(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file',
        ]);

        // Simulasi pembacaan data Excel
        DB::table('inventories')->insert([
            [
                'nama_bahan' => 'Susu Cair UHT Gizi',
                'kategori' => 'Lainnya',
                'stok' => 500,
                'satuan' => 'Pcs',
                'tanggal_masuk' => now()->toDateString(),
                'tanggal_kedaluwarsa' => now()->addMonths(6)->toDateString(),
                'status_stok' => 'Aman',
                'supplier_id' => 1,
                'harga' => 4500.00,
                'lokasi_penyimpanan' => 'Rak E-1',
                'barcode' => '899' . rand(10000000, 99999999),
                'qr_code' => 'INV-MILK-' . rand(100, 999),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_bahan' => 'Telur Ayam Broiler',
                'kategori' => 'Lauk',
                'stok' => 300,
                'satuan' => 'Butir',
                'tanggal_masuk' => now()->toDateString(),
                'tanggal_kedaluwarsa' => now()->addDays(14)->toDateString(),
                'status_stok' => 'Aman',
                'supplier_id' => 2,
                'harga' => 1800.00,
                'lokasi_penyimpanan' => 'Chiller C-2',
                'barcode' => '899' . rand(10000000, 99999999),
                'qr_code' => 'INV-EGG-' . rand(100, 999),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        ActivityLogger::log('IMPORT_INVENTORY', 'Mengimport data bahan makanan via simulator Excel.');

        return redirect()->back()->with('success', 'Data Excel berhasil diimport (Simulator)! Menambahkan 2 bahan: Susu Cair UHT & Telur Ayam.');
    }

    public function exportExcel()
    {
        $data = DB::table('inventories')->get();
        
        $filename = "inventaris_gudang_" . date('Ymd_His') . ".csv";
        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = array('ID', 'Nama Bahan', 'Kategori', 'Stok', 'Satuan', 'Harga Satuan', 'Tgl Masuk', 'Tgl Expired', 'Status');

        $callback = function() use($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($data as $item) {
                fputcsv($file, array(
                    $item->id,
                    $item->nama_bahan,
                    $item->kategori,
                    $item->stok,
                    $item->satuan,
                    $item->harga,
                    $item->tanggal_masuk,
                    $item->tanggal_kedaluwarsa,
                    $item->status_stok
                ));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportPdf()
    {
        $bahanMakanan = DB::table('inventories')
            ->leftJoin('suppliers', 'inventories.supplier_id', '=', 'suppliers.id')
            ->select('inventories.*', 'suppliers.name as nama_supplier')
            ->orderBy('inventories.nama_bahan', 'asc')
            ->get();

        // Menggunakan visual clean print HTML view agar PDF didownload/diprint lewat jendela browser secara premium
        return view('inventaris.print_pdf', compact('bahanMakanan'));
    }
}