<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'contact_name', 'phone', 'address'];

    public function inventories()
    {
        return $this->hasMany(Inventory::class, 'supplier_id');
    }
}
