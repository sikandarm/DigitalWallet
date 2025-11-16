<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use App\Events\TransactionBroadcast;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransactionService
{
    private const COMMISSION_RATE = 0.015;

    /**
     * Get transaction history for a user
     *
     * @param User $user
     * @param int $perPage
     * @return array
     */
    public function getTransactionHistory(User $user, int $perPage = 20): array
    {
        $transactions = Transaction::where('sender_id', $user->id)
            ->orWhere('receiver_id', $user->id)
            ->with(['sender:id,name,email', 'receiver:id,name,email'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return [
            'balance' => (float) $user->balance,
            'transactions' => $transactions,
        ];
    }

    /**
     * Execute a money transfer with atomic transaction and database locking
     *
     * @param User $sender
     * @param int $receiverId
     * @param float $amount
     * @return array
     * @throws \Exception
     */
    public function transferMoney(User $sender, int $receiverId, float $amount): array
    {
        $commissionFee = round($amount * self::COMMISSION_RATE, 2);
        $totalDebited = $amount + $commissionFee;

        DB::beginTransaction();

        try {
            $sender = User::where('id', $sender->id)
                ->lockForUpdate()
                ->first();

            if ($sender->balance < $totalDebited) {
                DB::rollBack();
                throw new \Exception('Insufficient balance', 422);
            }

            $receiver = User::where('id', $receiverId)
                ->lockForUpdate()
                ->first();

            if (!$receiver) {
                DB::rollBack();
                throw new \Exception('Receiver not found', 404);
            }

            $sender->balance -= $totalDebited;
            $receiver->balance += $amount;
            
            $sender->save();
            $receiver->save();

            $transaction = Transaction::create([
                'sender_id' => $sender->id,
                'receiver_id' => $receiver->id,
                'amount' => $amount,
                'commission_fee' => $commissionFee,
                'total_debited' => $totalDebited,
            ]);

            $sender->refresh();
            $receiver->refresh();

            DB::commit();

            event(new TransactionBroadcast($transaction, $sender, $receiver));

            return [
                'transaction' => $transaction->load(['sender:id,name,email', 'receiver:id,name,email']),
                'new_balance' => (float) $sender->balance,
                'commission_fee' => $commissionFee,
                'total_debited' => $totalDebited,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Transaction failed', [
                'sender_id' => $sender->id,
                'receiver_id' => $receiverId,
                'amount' => $amount,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Calculate commission fee
     *
     * @param float $amount
     * @return float
     */
    public function calculateCommission(float $amount): float
    {
        return round($amount * self::COMMISSION_RATE, 2);
    }
}

