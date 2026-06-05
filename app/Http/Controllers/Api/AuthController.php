<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        $user = \App\Models\User::with('posyandu')
            ->where('email', $credentials['email'])
            ->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password tidak sesuai.'],
            ]);
        }

        $token = $user->createToken($credentials['device_name'] ?? 'react-native')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $this->transformUser($user),
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user()->load('posyandu');

        return response()->json([
            'user' => $this->transformUser($user),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil.',
        ]);
    }

    protected function transformUser($user)
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'posyandu' => $user->posyandu ? [
                'id' => $user->posyandu->id,
                'name' => $user->posyandu->name,
                'code' => $user->posyandu->code,
                'village' => $user->posyandu->village,
            ] : null,
        ];
    }
}
