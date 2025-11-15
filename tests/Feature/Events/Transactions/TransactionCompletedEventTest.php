<?php

namespace Tests\Feature\Events\Transactions;

use App\Events\Transactions\TransactionCompleted;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Broadcast;
use Tests\TestCase;

class TransactionCompletedEventTest extends TestCase
{
    protected User $sender;
    protected User $receiver;
    protected Transaction $transaction;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sender = User::factory()->create();
        $this->receiver = User::factory()->create();
        $this->transaction = Transaction::factory()->create([
            'sender_id' => $this->sender->id,
            'receiver_id' => $this->receiver->id,
        ]);
    }

    public function test_broadcasts_transaction_completed_event()
    {
        Event::fake();

        $transaction = Transaction::factory()->create([
            'sender_id' => $this->sender->id,
            'receiver_id' => $this->receiver->id,
        ]);

        event(new TransactionCompleted($transaction));

        Event::assertDispatched(TransactionCompleted::class, function ($event) use ($transaction) {
            return $event->transaction->id === $transaction->id;
        });
    }

    public function test_broadcasts_to_sender_and_receiver_channels()
    {
        $event = new TransactionCompleted($this->transaction);

        $channels = $event->broadcastOn();

        $this->assertCount(2, $channels);
        $this->assertEquals('users.' . $this->sender->id, $channels[0]->name);
        $this->assertEquals('users.' . $this->receiver->id, $channels[1]->name);
    }


    public function test_includes_correct_data_in_broadcast_payload()
    {
        $event = new TransactionCompleted($this->transaction);

        $broadcastData = $event->broadcastWith();

        $this->assertArrayHasKey('transaction', $broadcastData);
        $this->assertArrayHasKey('type', $broadcastData);
        $this->assertEquals($this->transaction->id, $broadcastData['transaction']->id);
        $this->assertEquals($this->transaction->amount, $broadcastData['transaction']->amount);
    }

    public function test_uses_correct_event_name_for_broadcasting()
    {
        $event = new TransactionCompleted($this->transaction);

        $this->assertEquals('transaction.completed', $event->broadcastAs());
    }
}