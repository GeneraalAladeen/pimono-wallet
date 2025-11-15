<?php

namespace Tests\Feature\Jobs\Transaction;

use App\Events\Transactions\TransactionCompleted;
use App\Jobs\Transaction\ProcessTransfer;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class ProcessTransferJobTest extends TestCase
{
    public function test_job_processes_transfer(): void
    {
        Event::fake();

        $sender = User::factory()->create([
            'balance' => 200,
        ]);
        $receiver = User::factory()->create([
            'balance' => 0,
        ]);

        dispatch_sync(new ProcessTransfer($sender->id, $receiver->id, 100));

        $this->assertDatabaseHas('transactions', [
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'amount' => 100,
            'commission_fee' => 1.50,
            'total_amount_debited' => 101.50,
        ]);

        $transaction = Transaction::first();

        $this->assertDatabaseHas('users', [
            'id' => $receiver->id,
            'balance' => 100,
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $sender->id,
            'balance' => 98.5,
        ]);

        Event::assertDispatched(TransactionCompleted::class, function ($event) use ($transaction) {
            return $event->transaction->id === $transaction->id;
        });
    }
}
