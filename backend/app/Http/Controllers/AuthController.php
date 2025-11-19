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
        //$token = $userModel->createToken('auth_token', ['*'], now()->addMinutes(1))->plainTextToken;

        return response()->json(['token' => $token, 'user' => $user]);
    }

    // === LOGOUT ===
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }
}
