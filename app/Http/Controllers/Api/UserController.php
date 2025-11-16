<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ErrorResource;
use App\Http\Resources\UserResource;
use App\Services\UserService;

class UserController extends Controller
{
    public function __construct(
        private UserService $userService
    ) {}

    /**
     * Get user by email
     */
    public function getByEmail(string $email)
    {
        $user = $this->userService->getUserByEmail($email);

        if (!$user) {
            return new ErrorResource(['message' => 'User not found'], 404);
        }

        return new UserResource($user);
    }
}
