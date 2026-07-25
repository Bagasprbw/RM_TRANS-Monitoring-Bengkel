<?php

namespace App\Http\Controllers\Api\Authentikasi;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * @group Authentication
 *
 * APIs for managing user authentication (login, logout, profile)
 */
class AuthController extends Controller
{
    /**
     * Login user
     *
     * Authenticate user and return API token.
     *
     * @bodyParam username string required Username.
     * @bodyParam password string required Password.
     *
     * @response {
     *   "message": "Login berhasil",
     *   "token": "...",
     *   "user": {...}
     * }
     */
    public function login(Request $request)
    {
        // Validasi
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        // Cari user
        $user = User::where('username', $request->username)->first();

        // Cek user & password
        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Username atau password salah',
            ], 401);
        }

        // Generate token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'token' => $token,
            'user' => $user,
        ]);
    }

    /**
     * Logout user
     *
     * Invalidate current access token.
     *
     * @response {
     *   "message": "Logout berhasil"
     * }
     */
    public function logout(Request $request)
    {
        // Cek apakah user ada (token valid)
        if (! $request->user()) {
            return response()->json([
                'message' => 'Token tidak ditemukan atau tidak valid',
            ], 401);
        }

        // Hapus token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil',
        ]);
    }

    /**
     * Update profile
     *
     * Update username and optionally change password.
     *
     * @bodyParam username string required Username (must be unique).
     * @bodyParam current_password string Required if changing password.
     * @bodyParam password string Required if changing, min 6 characters, must be confirmed.
     * @bodyParam password_confirmation string Required if changing password.
     *
     * @response {
     *   "message": "Profil berhasil diperbarui",
     *   "user": {...}
     * }
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'username' => 'required|unique:users,username,'.$user->id,
            'current_password' => 'nullable|required_with:password',
            'password' => 'nullable|min:6|confirmed',
        ]);

        if ($request->filled('password')) {
            if (! Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'message' => 'Password lama tidak sesuai',
                ], 400);
            }
            $user->password = Hash::make($request->password);
        }

        $user->username = $request->username;
        $user->save();

        return response()->json([
            'message' => 'Profil berhasil diperbarui',
            'user' => $user,
        ]);
    }
}
