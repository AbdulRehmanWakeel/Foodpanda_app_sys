<?php

namespace App\Services;

use App\Models\User;
use App\Services\Contracts\AuthServiceInterface;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService implements AuthServiceInterface
{
    public function register(array $data): User
    {
        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $user->assignRole($data['role']);
        return $user;  
    }

    public function login(array $data): array
    {
        if (!$token = JWTAuth::attempt([
            'email'    => $data['email'],
            'password' => $data['password'],
        ])) {
            throw new \Exception('Invalid credentials.');
        }

        return [
            'token' => $token,
            'user'  => auth()->user()->load('roles'),
        ];
    }

    public function logout(): bool
    {
        JWTAuth::invalidate(JWTAuth::getToken());
        return true;
    }

    public function profile(): User
    {
        return auth()->user()->load('roles');
    }

     
}
