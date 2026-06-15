<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WasteLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'distribution_id',
        'qty_habis',
        'qty_sebagian',
        'qty_tidak_habis',
        'food_waste_index',
        'recommendation_status',
        'recommended_next_qty'
    ];

    public function distribution()
    {
        return $this->belongsTo(Distribution::class, 'distribution_id');
    }
}