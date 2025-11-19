# Belajar Laravel 12

## 1. Buat Database dan Tabel
Buat database anggaplah nama Database `pos_db` Karena kita menggunakan SQL Manual ketikkan perintah berikut :

> CREATE TABLE users (  
> userid CHAR(4) NOT NULL PRIMARY KEY,  
> name VARCHAR(255) NOT NULL,  
> password VARCHAR(255) NOT NULL,  
> created_at TIMESTAMP NULL,  
> updated_at TIMESTAMP NULL
> );

> CREATE TABLE personal_access_tokens (  
> id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,  
> tokenable_type VARCHAR(255) NOT NULL,  
> tokenable_id CHAR(4) NOT NULL,  
> name VARCHAR(255) NOT NULL,  
> token VARCHAR(64) UNIQUE NOT NULL,  
> abilities TEXT,  
> last_used_at TIMESTAMP NULL,  
> expires_at TIMESTAMP NULL,  
> created_at TIMESTAMP NULL,  
> updated_at TIMESTAMP NULL,  
> INDEX tokenable_index (tokenable_type, tokenable_id)
> );

Keterangan :  
    1. Tabel users : digunakan untuk menyimpan data user dan verifikasi password  
    2. Tabel personal_access_tokens : digunakan untuk menyimpan hasil token dari sacntum laravel nanti

## 2. Install laravel dengan perintah berikut
`composer create-project laravel/laravel:^12 backend` <br>

Artinya :
> **composer** : Ini adalah perintah untuk menjalankan aplikasi Composer  
> **create-project** : Ini adalah instruksi Composer untuk membuat proyek baru dari paket/repositori tertentu  
> **laravel/laravel** : Ini adalah nama paket (package name) yang tersedia di repositori Packagist (tempat paket PHP disimpan).  
> **:^12** : Ini menentukan versi Laravel yang ingin diinstal. Simbol ^ (caret) berarti menginstal versi terbaru yang kompatibel dengan versi utama 12 (misalnya, 12.0, 12.1, dst).  
> **backend** : Ini adalah nama direktori atau folder tempat proyek Laravel akan dibuat dan disimpan di komputer Anda. Anda bisa mengganti nama ini sesuai keinginan, misalnya my-project atau api-project

## 3. Instal Sanctum di Laravel 12
Masuk ke folder backend, lalu jalankan `composer require laravel/sanctum`
Kemudian di file bootstrap/app.php, tambahkan middleware Sanctum ke grup api:  

```
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// Tamabahn
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php', // Harus ada
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->api(prepend: [EnsureFrontendRequestsAreStateful::class]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
```

## 4. Aktifkan HasApiTokens di model User
Edit app/Models/User.php seperti ini :
```
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
```

## 5. Route API
Kalau tidak ada routes/api.php, maka buat saja filenya :
```
<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (\Illuminate\Http\Request $request) {
        return $request->user();
    });
});
```

## 6. Controller AuthController.php
Buat app/Http/Controllers/AuthController.php :
```
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    // === REGISTER ===
    public function register(Request $request)
    {
        $request->validate([
            'userid' => 'required|string|max:4',
            'name' => 'required|string|max:255',
            'password' => 'required|min:4',
        ]);

        // Simpan pakai RAW SQL
        DB::insert('INSERT INTO users (userid, name, password, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())', [
            $request->userid,
            $request->name,
            Hash::make($request->password),
        ]);

        return response()->json(['message' => 'User registered successfully']);
    }

    // === LOGIN ===
    public function login(Request $request)
    {
        $request->validate([
            'userid' => 'required',
            'password' => 'required',
        ]);

        // Ambil user dengan RAW SQL
        $user = DB::selectOne('SELECT * FROM users WHERE userid = ?', [$request->userid]);

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // Convert hasil ke model agar bisa pakai Sanctum
        // $userModel = User::find($user->userid); ==>> GANTI karena kolom users berubah
        $userModel = User::where('userid', $user->userid)->first();
        // $token = $userModel->createToken('auth_token')->plainTextToken;
        $token = $userModel->createToken('auth_token', ['*'], now()->addHours(1))->plainTextToken;

        return response()->json(['token' => $token, 'user' => $user]);
    }

    // === LOGOUT ===
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }
}
```
Di sini kita menggunakan RAW SQL untuk Register & Login, tapi Logout tetap memakai Eloquent karena token Sanctum disimpan lewat model.

## 7. Uji Coba API
Gunakan Postman
1. POST http://localhost:8000/api/register  
Body (JSON) : `{"userid": "0236", "name": "Firman", "password": "0236"}`
2. POST http://localhost:8000/api/login  
Body (JSON) : `{"userid": "0236", "password": "0236"}`
3. POST http://localhost:8000/api/logout  
Header : `{"Accept": "application/json", "Authorization": "Bearer 2|XIqf2euJQ7iMBeCnq5Q9EBjoQWPE7LWgnAELrnaS647d4573"}`