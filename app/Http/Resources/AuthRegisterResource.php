<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthRegisterResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'message' => 'Registration successful',
            'user' => new UserResource($this->resource['user']),
            'token' => $this->resource['token'],
        ];
    }

    public function withResponse(Request $request, $response): void
    {
        $response->setStatusCode(201);
    }
}

