<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthLoginResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'message' => 'Login successful',
            'user' => new UserResource($this->resource['user']),
            'token' => $this->resource['token'],
        ];
    }
}

