<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTransactionRequest;
use App\Http\Resources\ErrorResource;
use App\Http\Resources\TransactionIndexResource;
use App\Http\Resources\TransactionStoreResource;
use App\Services\TransactionService;

class TransactionController extends Controller
{
    public function __construct(
        private TransactionService $transactionService
    ) {}

    public function index()
    {
        $user = request()->user();
        $result = $this->transactionService->getTransactionHistory($user);

        return new TransactionIndexResource($result);
    }

    /**
     * Execute a new money transfer with atomic transaction and database locking
     */
    public function store(StoreTransactionRequest $request)
    {
        $user = $request->user();
        $validated = $request->validated();
        $receiverId = (int) $validated['receiver_id'];
        $amount = (float) $validated['amount'];

        try {
            $result = $this->transactionService->transferMoney($user, $receiverId, $amount);

            return new TransactionStoreResource($result);

        } catch (\Exception $e) {
            $statusCode = $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500;
            
            if ($statusCode === 422) {
                $commissionFee = $this->transactionService->calculateCommission($amount);
                $totalDebited = $amount + $commissionFee;
                
                return new ErrorResource([
                    'message' => 'Insufficient balance',
                    'errors' => [
                        'balance' => [
                            'Your current balance is ' . number_format($user->balance, 2) . 
                            '. You need ' . number_format($totalDebited, 2) . 
                            ' (including ' . number_format($commissionFee, 2) . ' commission fee).'
                        ]
                    ],
                ], 422);
            }

            if ($statusCode === 404) {
                return new ErrorResource(['message' => 'Receiver not found'], 404);
            }

            return new ErrorResource([
                'message' => 'Transfer failed',
                'error' => 'An error occurred while processing the transfer. Please try again.',
            ], 500);
        }
    }
}
