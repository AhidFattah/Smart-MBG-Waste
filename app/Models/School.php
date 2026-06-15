<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    use HasFactory;

    protected $fillable = ['school_code', 'name', 'address', 'total_students'];

    public function users()
    {
        return $this->hasMany(User::class, 'school_id');
    }

    public function distributions()
    {
        return $this->hasMany(Distribution::class, 'school_id');
    }
}