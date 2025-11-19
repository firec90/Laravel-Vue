<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // Tambahan
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';
    protected $primaryKey = 'kode_barang';
    protected $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['kode_barang', 'nama_barang', 'harga'];
}

// Kode diatas digunakan pada Controller apabila tidak menggunakan RAW SQL akan tetapi menggunakan Eloquent ORM