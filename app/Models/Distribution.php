<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Distribution extends Model
{
    use HasFactory;

    protected $fillable = ['distribution_date', 'school_id', 'menu_id', 'qty_sent', 'status'];

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
}