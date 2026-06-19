<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FoodWaste extends Model
{
    use HasFactory;

    protected $table = 'food_wastes';

    protected $fillable = [
        'distribution_id',
        'berat_sisa_makanan',
        'jumlah_sisa_porsi',
        'persentase_waste',
        'penyebab_waste',
        'kategori_waste'
    ];

    public function distribution()
    {
        return $this->belongsTo(Distribution::class, 'distribution_id');
    }
}
