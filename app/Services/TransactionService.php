<?php

namespace App\Services;

use App\Events\Transactions\TransactionCompleted;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    const COMMISSION_RATE = 0.015;

    /**
     * Transfer money to user
     *
     * @param  int  $senderId
     * @param  int  $receiverId
     * @param  float  $amount
     * @return Transaction
     *
     * @throws \Exception
     */
    public function executeTransfer($senderId, $receiverId, $amount)
    {
        return DB::transaction(function () use ($senderId, $receiverId, $amount) {

            $sender = User::where('id', $senderId)->lockForUpdate()->first();
            $receiver = User::where('id', $receiverId)->lockForUpdate()->first();

            $commissionFee = $amount * self::COMMISSION_RATE;
            $totalAmountDebited = $amount + $commissionFee;

            $sender->balance -= $totalAmountDebited;
            $receiver->balance += $amount;

            $sender->save();
            $receiver->save();

            $transaction = Transaction::create([
                'sender_id' => $senderId,
                'receiver_id' => $receiverId,
                'amount' => $amount,
                'commission_fee' => $commissionFee,
                'total_amount_debited' => $totalAmountDebited,
            ]);

            $transaction->setRelation('sender', $sender);
            $transaction->setRelation('receiver', $receiver);

            broadcast(new TransactionCompleted($transaction));

            return $transaction;
        });
    }

    /**
     * Fetches User Transactions
     *
     *
     * @return \Illuminate\Database\Eloquent\Collection<Transaction>
     */
    public function getUserTransactions(User $user, ?int $perPage = 10)
    {
        $transactions = Transaction::where('sender_id', $user->id)
            ->orWhere('receiver_id', $user->id)
            ->with(['sender', 'receiver'])
            ->orderBy('created_at', 'desc')
            ->simplePaginate($perPage);

        return $transactions;
    }
}
