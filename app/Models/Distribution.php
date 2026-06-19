<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Distribution extends Model
{
    use HasFactory;

    protected $fillable = [
        'tanggal_distribusi',
        'school_id',
        'menu_id',
        'total_porsi_dikirim',
        'status_pengiriman',
        'jumlah_siswa_hadir',
        'persentase_cadangan',
        'kendaraan_distribusi',
        'petugas_distribusi',
        'waktu_distribusi',
        'qr_code_surat_jalan',
        'waktu_diterima',
        'penerima_nama'
    ];

    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }

    public function wasteLog()
    {
        return $this->hasOne(WasteLog::class, 'distribution_id');
    }

    public function foodWaste()
    {
        return $this->hasOne(FoodWaste::class, 'distribution_id');
    }
}