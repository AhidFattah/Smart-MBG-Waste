<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_bahan',
        'kategori',
        'stok',
        'satuan',
        'tanggal_masuk',
        'tanggal_kedaluwarsa',
        'status_stok',
        'supplier_id',
        'harga',
        'lokasi_penyimpanan',
        'barcode',
        'qr_code',
        'foto_bahan'
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function logs()
    {
        return $this->hasMany(InventoryLog::class, 'inventory_id');
    }
}
