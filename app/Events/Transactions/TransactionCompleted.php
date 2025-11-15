<?php

namespace App\Events\Transactions;

use App\Models\Transaction;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TransactionCompleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Transaction $transaction)
    {
        //
    }

    public function broadcastOn()
    {
        return [
            new PrivateChannel('users.'.$this->transaction->sender_id),
            new PrivateChannel('users.'.$this->transaction->receiver_id),
        ];
    }

    public function broadcastAs()
    {
        return 'transaction.completed';
    }

    public function broadcastWith()
    {
        return [
            'transaction' => $this->transaction,
            'type' => $this->getTransactionTypeForUser(auth()->id()),
        ];
    }

    private function getTransactionTypeForUser($userId)
    {
        if ($this->transaction->sender_id == $userId) {
            return 'sent';
        }

        return 'received';
    }
}
