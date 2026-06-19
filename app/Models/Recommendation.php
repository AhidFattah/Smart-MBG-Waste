<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recommendation extends Model
{
    use HasFactory;

    protected $fillable = ['tipe', 'school_id', 'message'];

    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }
}
