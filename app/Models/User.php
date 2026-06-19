<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'role_id',
        'school_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Relasi Ke Tabel Sekolah (Satu user terikat ke satu sekolah, kecuali Admin/Dapur)
    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    // Relasi Ke Tabel Peran (Role)
    public function roleModel()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }
}