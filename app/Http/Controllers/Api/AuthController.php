<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\AuthLoginResource;
use App\Http\Resources\AuthLogoutResource;
use App\Http\Resources\AuthRegisterResource;
use App\Http\Resources\ErrorResource;
use App\Services\AuthService;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {}

    /**
     * Register a new user
     */
    public function register(RegisterRequest $request)
    {
        $result = $this->authService->register($request->validated());

        return new AuthRegisterResource($result);
    }

    /**
     * Login user
     */
    public function login(LoginRequest $request)
    {
        $validated = $request->validated();
        $result = $this->authService->login($validated['email'], $validated['password']);

        if (!$result) {
            return new ErrorResource(['message' => 'Invalid credentials'], 401);
        }

        return new AuthLoginResource($result);
    }

    /**
     * Logout user
     */
    public function logout()
    {
        $this->authService->logout(request()->user());

        return new AuthLogoutResource([]);
    }
}
