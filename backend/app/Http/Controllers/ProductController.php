<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Product; // belum dipakai sama sekali oleh Controller

class ProductController extends Controller
{
    // CREATE / INSERT
    public function store(Request $request)
    {
        $request->validate([
            'kode_barang' => 'required|string|max:5',
            'nama_barang' => 'required|string|max:100',
            'harga' => 'required|numeric|min:0',
        ]);

        DB::insert('INSERT INTO products (kode_barang, nama_barang, harga, created_at, updated_at)
                    VALUES (?, ?, ?, NOW(), NOW())', [
            $request->kode_barang,
            $request->nama_barang,
            $request->harga,
        ]);

        return response()->json(['message' => 'Product created successfully']);
    }

    // READ / GET ALL
    public function index()
    {
        $products = DB::select('SELECT * FROM products ORDER BY kode_barang ASC');
        return response()->json($products);
    }

    // UPDATE
    public function update(Request $request, $kode_barang)
    {
        $request->validate([
            'nama_barang' => 'required|string|max:100',
            'harga' => 'required|numeric|min:0',
        ]);

        DB::update('UPDATE products SET nama_barang = ?, harga = ?, updated_at = NOW() WHERE kode_barang = ?', [
            $request->nama_barang,
            $request->harga,
            $kode_barang,
        ]);

        return response()->json(['message' => 'Product updated successfully']);
    }

    // DELETE
    public function destroy($kode_barang)
    {
        DB::delete('DELETE FROM products WHERE kode_barang = ?', [$kode_barang]);
        return response()->json(['message' => 'Product deleted successfully']);
    }
}
