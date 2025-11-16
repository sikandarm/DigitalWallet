<?php

namespace App\Events;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TransactionBroadcast implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $transaction;
    public $sender;
    public $receiver;

    /**
     * Create a new event instance.
     */
    public function __construct(Transaction $transaction, User $sender, User $receiver)
    {
        $this->transaction = $transaction;
        $this->sender = [
            'id' => $sender->id,
            'balance' => (float) $sender->balance,
        ];
        $this->receiver = [
            'id' => $receiver->id,
            'balance' => (float) $receiver->balance,
        ];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->transaction->sender_id),
            new PrivateChannel('user.' . $this->transaction->receiver_id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'transaction.completed';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'transaction' => [
                'id' => $this->transaction->id,
                'sender_id' => $this->transaction->sender_id,
                'receiver_id' => $this->transaction->receiver_id,
                'amount' => (float) $this->transaction->amount,
                'commission_fee' => (float) $this->transaction->commission_fee,
                'total_debited' => (float) $this->transaction->total_debited,
                'created_at' => $this->transaction->created_at->toISOString(),
                'sender' => [
                    'id' => $this->transaction->sender->id,
                    'name' => $this->transaction->sender->name,
                    'email' => $this->transaction->sender->email,
                ],
                'receiver' => [
                    'id' => $this->transaction->receiver->id,
                    'name' => $this->transaction->receiver->name,
                    'email' => $this->transaction->receiver->email,
                ],
            ],
            'sender_balance' => $this->sender['balance'],
            'receiver_balance' => $this->receiver['balance'],
        ];
    }
}
