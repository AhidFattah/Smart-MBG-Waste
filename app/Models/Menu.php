<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;

    protected $fillable = ['menu_code', 'menu_name', 'calories'];

    public function distributions()
    {
        return $this->hasMany(Distribution::class, 'menu_id');
    }
}