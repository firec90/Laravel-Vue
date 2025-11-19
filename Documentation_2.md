# Belajar Laravel 12 (INPUT, UPDATE & DELETE)
## 1. Buat tabel `products`
> CREATE TABLE products (  
> kode_barang VARCHAR(5) PRIMARY KEY,  
> nama_barang VARCHAR(100) NOT NULL,  
> harga DECIMAL(10,2) NOT NULL,  
> created_at TIMESTAMP NULL,  
> updated_at TIMESTAMP NULL);

## 2. Buat model Product
Jalankan perintah `php artisan make:model Product`  
Lalu edit file `app/Models/Product.php` :
```
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
```

## 3. Buat Controller ProductController
Jalankan perintah berikut `php artisan make:controller ProductController`  
Edit file `app/Http/Controllers/ProductController.php` :
```
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
```

## 4. Tambahkan route API
Buka file routes/api.php dan tambahkan menjadi seperti berikut :
```
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (\Illuminate\Http\Request $request) {
        return $request->user();
    });

    Route::get('/products', [ProductController::class, 'index']);      // GET semua produk
    Route::post('/products', [ProductController::class, 'store']);     // Tambah produk
    Route::put('/products/{kode_barang}', [ProductController::class, 'update']);  // Update
    Route::delete('/products/{kode_barang}', [ProductController::class, 'destroy']); // Hapus
});
```

## 5. Uji di Postman
Jangan lupa setiap kali mengoperasikan tabel `products` harus login terlebih dahulu agar mendapatkan token. Lalu setiap kali INSERT, UPDATE dan DELETE data sertakan Header seperti berikut :  
Accept: application/json  
Authorization: Bearer <token>

### Contoh request :
* Lihat semua produk  
    GET http://localhost:8000/api/products  

* Tambah data
    POST http://localhost:8000/api/products  
    Body (JSON):  
    {  
    "kode_barang": "A0001",  
    "nama_barang": "Indomie Goreng",  
    "harga": 3500  
    }

* Update data  
    PUT http://localhost:8000/api/products/A0001  
    Body (JSON):  
    {  
    "nama_barang": "Indomie Goreng Spesial",  
    "harga": 4000  
    }

* Hapus data  
    DELETE http://localhost:8000/api/products/A0001