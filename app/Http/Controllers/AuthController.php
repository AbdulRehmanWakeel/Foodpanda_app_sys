<?php

namespace App\Http\Controllers;

use App\Services\Contracts\AuthServiceInterface;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected AuthServiceInterface $authService;

    public function __construct(AuthServiceInterface $authService)
    {
        $this->authService = $authService;
    }

    public function register(Request $request)
    {
        try {
            $request->validate([
                'name'     => 'required|string|max:255',
                'email'    => 'required|string|email|unique:users',
                'password' => 'required|string|min:6',
                'role'     => 'required|string|in:customer,restaurant,rider,admin',
            ]);

            $user = $this->authService->register($request->all());

            return response()->json([
                'message' => 'User registered successfully',
                'user'    => $user->load('roles')
            ], 201);

        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'email'    => 'required|email',
                'password' => 'required|string',
                'role'     => 'required|string|in:customer,restaurant,rider,admin',
            ]);

            $data = $this->authService->login($request->all());

            return response()->json([
                'message' => 'Login successful',
                'user'    => $data['user'],
                'token'   => $data['token'],
            ], 200);

        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 401);
        }
    }

    public function logout()
    {
        try {
            $this->authService->logout();
            return response()->json(['message' => 'Logged out successfully'], 200);

        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function profile()
    {
        return response()->json($this->authService->profile(), 200);
    }

}