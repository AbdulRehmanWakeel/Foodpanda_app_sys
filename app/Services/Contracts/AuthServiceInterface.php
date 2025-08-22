<?php

namespace App\Services\Contracts;

use Illuminate\Http\Request;

interface AuthServiceInterface {
    public function register(array $data);
    public function login(array $data);
    public function logout();
}
