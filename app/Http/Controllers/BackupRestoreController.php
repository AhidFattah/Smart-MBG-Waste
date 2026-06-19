<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use App\Helpers\ActivityLogger;

class BackupRestoreController extends Controller
{
    protected $tables = [
        'roles', 'schools', 'users', 'menus', 'suppliers', 
        'inventories', 'inventory_logs', 'distributions', 
        'food_wastes', 'food_waste_logs', 'recommendations', 
        'notifications', 'activity_logs', 'settings'
    ];

    public function index()
    {
        $backupDir = storage_path('app/backups');
        if (!File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $files = File::files($backupDir);
        $backups = [];
        foreach ($files as $file) {
            $backups[] = [
                'filename' => $file->getFilename(),
                'size' => round($file->getSize() / 1024, 2) . ' KB',
                'created_at' => date('d M Y H:i:s', $file->getMTime())
            ];
        }

        // Urutkan backup terbaru di atas
        usort($backups, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        return view('admin.backup', compact('backups'));
    }

    // A. MENJALANKAN BACKUP PORTABLE (JSON EXPORT)
    public function runBackup()
    {
        try {
            $backupData = [];
            foreach ($this->tables as $table) {
                if (DB::getSchemaBuilder()->hasTable($table)) {
                    $backupData[$table] = DB::table($table)->get()->toArray();
                }
            }

            $backupDir = storage_path('app/backups');
            if (!File::exists($backupDir)) {
                File::makeDirectory($backupDir, 0755, true);
            }

            $filename = 'backup_mbg_' . date('Ymd_His') . '.json';
            $filePath = $backupDir . '/' . $filename;
            
            File::put($filePath, json_encode($backupData, JSON_PRETTY_PRINT));
            
            ActivityLogger::log('BACKUP_DATABASE', 'Membuat backup database: ' . $filename);

            return redirect()->back()->with('success', 'Backup database berhasil dibuat! File: ' . $filename);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal membackup database: ' . $e->getMessage());
        }
    }

    // B. MENJALANKAN RESTORE DATABASE DARI FILE UPLOAD / LOG
    public function runRestore(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file',
        ]);

        $file = $request->file('backup_file');
        
        try {
            $content = File::get($file->getRealPath());
            $backupData = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE || !is_array($backupData)) {
                return redirect()->back()->with('error', 'Format file backup tidak valid. Harap unggah file backup berformat JSON sistem.');
            }

            // Matikan foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS = 0;');

            foreach ($this->tables as $table) {
                if (isset($backupData[$table])) {
                    // Truncate tabel
                    DB::table($table)->truncate();
                    
                    // Insert data backup (cast array to object or map arrays)
                    $insertData = [];
                    foreach ($backupData[$table] as $row) {
                        $insertData[] = (array) $row;
                    }
                    
                    if (count($insertData) > 0) {
                        // Chunk insert jika record sangat banyak
                        foreach (array_chunk($insertData, 100) as $chunk) {
                            DB::table($table)->insert($chunk);
                        }
                    }
                }
            }

            // Hidupkan kembali foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS = 1;');

            ActivityLogger::log('RESTORE_DATABASE', 'Merestore database dari file: ' . $file->getClientOriginalName());

            // Tambahkan notifikasi restore berhasil
            DB::table('notifications')->insert([
                'tipe' => 'success',
                'message' => '✅ RESTORE SUKSES: Database berhasil dikembalikan ke kondisi cadangan!',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return redirect()->back()->with('success', 'Database berhasil direstore sepenuhnya dari file backup!');
        } catch (\Exception $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
            return redirect()->back()->with('error', 'Gagal merestore database: ' . $e->getMessage());
        }
    }

    // C. DOWNLOAD FILE BACKUP
    public function downloadBackup($file)
    {
        $path = storage_path('app/backups/' . $file);
        if (File::exists($path)) {
            return response()->download($path);
        }
        return redirect()->back()->with('error', 'File backup tidak ditemukan.');
    }

    // D. DELETE FILE BACKUP
    public function deleteBackup($file)
    {
        $path = storage_path('app/backups/' . $file);
        if (File::exists($path)) {
            File::delete($path);
            ActivityLogger::log('DELETE_BACKUP', 'Menghapus file backup cadangan: ' . $file);
            return redirect()->back()->with('success', 'File backup berhasil dihapus.');
        }
        return redirect()->back()->with('error', 'File tidak ditemukan.');
    }
}
