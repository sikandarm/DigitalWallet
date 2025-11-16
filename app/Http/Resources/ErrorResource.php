<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ErrorResource extends JsonResource
{
    private int $statusCode;

    public function __construct($resource, int $statusCode = 400)
    {
        parent::__construct($resource);
        $this->statusCode = $statusCode;
    }

    public function toArray(Request $request): array
    {
        $data = is_array($this->resource) ? $this->resource : ['message' => $this->resource];
        
        return array_merge([
            'message' => $data['message'] ?? 'An error occurred',
        ], $data);
    }

    public function withResponse(Request $request, $response): void
    {
        $response->setStatusCode($this->statusCode);
    }
}

