<?php

namespace App\Services;

use App\Contracts\CircuitBreakerInterface;
use App\Contracts\TransactionInterface;
use App\Events\Transactions\TransactionCompleted;
use App\Jobs\Transaction\ProcessTransfer;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

class TransactionService implements TransactionInterface
{
    const COMMISSION_RATE = 0.015;

    public function __construct(public CircuitBreakerInterface $circuitBreaker)
    {
        //
    }

    /**
     * Transfer money to user
     *
     * @param  int  $senderId
     * @param  int  $receiverId
     * @param  float  $amount
     * @return Transaction | array
     *
     * @throws \Exception
     */
    public function transferMoney($senderId, $receiverId, $amount)
    {
        if ($this->shouldProcessSync()) {
            return $this->processTransferSync($senderId, $receiverId, $amount);
        }

        ProcessTransfer::dispatch($senderId, $receiverId, $amount);

        return ['status' => 'queued', 'message' => 'Transfer queued for processing'];
    }

    public function shouldProcessSync(): bool
    {
        return $this->isLowLoad() &&
               $this->isCircuitHealthy() &&
               $this->isQueueEmptyEnough();
    }

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

    public function isLowLoad()
    {
        $load = sys_getloadavg()[0]; // 1-minute system load average
        $maxLoad = config('app.max_system_load', 2.0);

        return $load < $maxLoad;
    }

    public function isCircuitHealthy()
    {
        return $this->circuitBreaker->isAvailable();
    }

    private function isQueueEmptyEnough()
    {
        $queueSize = Queue::size('transfers');
        $maxQueueSize = config('app.max_queue_size', 50);

        return $queueSize < $maxQueueSize;
    }

    public function processTransferSync($senderId, $receiverId, $amount)
    {
        try {
            $result = $this->executeTransfer($senderId, $receiverId, $amount);

            $this->circuitBreaker->markSuccess();

            return $result;

        } catch (\Exception $e) {

            $this->circuitBreaker->markFailure();

            if ($this->isSystemOverload($e)) {
                throw new SystemOverloadException($e->getMessage());
            }

            throw $e;
        }
    }

    public function isSystemOverload(\Exception $e)
    {
        $overloadIndicators = [
            'SQLSTATE[40001]',
            'Deadlock found',
            'Connection timeout',
            'Too many connections',
            'Server has gone away',
        ];

        foreach ($overloadIndicators as $indicator) {
            if (str_contains($e->getMessage(), $indicator)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Fetches User Transactions
     *
     *
     * @return \Illuminate\Database\Eloquent\Collection<Transaction>
     */
    public function getUserTransactions(User $user, int $perPage)
    {
        $transactions = Transaction::where('sender_id', $user->id)
            ->orWhere('receiver_id', $user->id)
            ->with(['sender', 'receiver'])
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->cursorPaginate($perPage);

        return $transactions;
    }
}
