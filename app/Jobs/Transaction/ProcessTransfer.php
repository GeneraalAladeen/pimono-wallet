<?php

namespace App\Jobs\Transaction;

use App\Services\TransactionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessTransfer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $senderId, public int $receiverId, public float $amount)
    {
        $this->onQueue('transfers');
    }

    public function handle(TransactionService $transactionService)
    {
        $transactionService->executeTransfer(
            $this->senderId,
            $this->receiverId,
            $this->amount
        );
    }

    public function failed(\Exception $exception)
    {
        \Log::error('Queued transfer failed: '.$exception->getMessage());
    }
}
