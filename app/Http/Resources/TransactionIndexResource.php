<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionIndexResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'balance' => $this->resource['balance'],
            'transactions' => TransactionCollection::make($this->resource['transactions'])->response($request)->getData(true),
        ];
    }
}

