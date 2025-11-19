<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// Tambahan
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // Tambahkan baris ini agar bisa mengenali tabel users secara eksplisit
    protected $table = 'users';

     // ✅ Tambahkan ini:
    protected $primaryKey = 'userid';   // ganti primary key default
    public $incrementing = false;       // karena CHAR(4), bukan auto-increment
    protected $keyType = 'string';      // karena tipe kolom CHAR/VARCHAR

    protected $fillable = ['userid','name','password'];
    protected $hidden = ['password','remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
