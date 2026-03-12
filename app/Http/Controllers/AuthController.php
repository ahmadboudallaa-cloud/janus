<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\LogoutRequest;
use App\Http\Requests\Auth\MeRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use ApiResponse;

    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->success([
            'user' => $user,
            'token' => $token,
        ], 'Registration successful.');
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = User::where('email', $data['email'])->first();

        if ($user === null || ! Hash::check($data['password'], $user->password)) {
            return $this->error(
                ['credentials' => ['Invalid email or password.']],
                'Invalid credentials.',
                401
            );
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->success([
            'user' => $user,
            'token' => $token,
        ], 'Login successful.');
    }

    public function logout(LogoutRequest $request): JsonResponse
    {
        $user = $request->user();

        if ($user === null) {
            return $this->error(new \stdClass(), 'Unauthenticated.', 401);
        }

        $currentToken = $user->currentAccessToken();

        if ($currentToken !== null) {
            // Revoke only the current token to keep other sessions alive.
            $currentToken->delete();
        } else {
            // Fallback: revoke all tokens if the current token cannot be resolved.
            $user->tokens()->delete();
        }

        return $this->success(null, 'Logout successful.');
    }

    public function me(MeRequest $request): JsonResponse
    {
        return $this->success([
            'user' => $request->user(),
        ], 'Authenticated user.');
    }
}
