<?php

namespace Tests\Feature\Api\Auth;

use App\Models\User;
use App\Models\Transaction;
use Tests\TestCase;
use Illuminate\Support\Facades\Queue;
use App\Jobs\Transaction\ProcessTransfer;
use Mockery;
use Mockery\MockInterface;
use App\Services\TransactionService;
use App\Contracts\TransactionInterface;
use App\Contracts\CircuitBreakerInterface;
use App\Events\Transactions\TransactionCompleted;
use Illuminate\Support\Facades\Event;
use Illuminate\Broadcasting\Channel;


class StoreTransactionTest extends TestCase
{
    public function test_user_must_be_authenticated(): void
    {
        $receiver = User::factory()->create();

        $response = $this->postJson(route('transaction.store'), [
            'receiver_id' => $receiver->id,
            'amount' => 100,
        ])->assertUnauthorized();
    }

    public function test_cannot_transfer_when_balance_is_insuffienct(): void
    {
        $receiver = User::factory()->create();
        $sender = User::factory()->create([
            'balance' => 10.00
        ]);

        $this->actingAs($sender);

        $response = $this->postJson(route('transaction.store'), [
            'receiver_id' => $receiver->id,
            'amount' => 100,
        ]);

        $response->assertUnprocessable()
        ->assertJsonValidationErrors([
            'amount' => 'Insufficient balance including commission fee.'
        ]);
    }

    public function test_cannot_transfer_to_a_user_that_doesnt_exist(): void
    {
        $sender = User::factory()->create();
        $this->actingAs($sender);

        $response = $this->postJson(route('transaction.store'), [
            'receiver_id' => 11111,
            'amount' => 100,
        ]);

        $response->assertUnprocessable()
        ->assertJsonValidationErrors([
            'receiver_id'
        ]);
    }

    public function test_cannot_transfer_money_to_yourself(): void
    {
        $sender = User::factory()->create([
            'balance' => 1000
        ]);
        $this->actingAs($sender);

        $response = $this->postJson(route('transaction.store'), [
            'receiver_id' => $sender->id,
            'amount' => 10,
        ]);

        $response->assertUnprocessable()
        ->assertJsonValidationErrors([
            'receiver_id' => 'You cannot transfer money to yourself.'
        ]);
    }

    public function test_transfer_can_be_made_to_user(): void
    {
        Event::fake();

        $sender = User::factory()->create([
            'balance' => 200
        ]);
        $receiver = User::factory()->create([
            'balance' => 0
        ]);

        $this->actingAs($sender);

        $response = $this->postJson(route('transaction.store'), [
            'receiver_id' => $receiver->id,
            'amount' => 100,
        ]);

        $response->assertCreated()
        ->assertJsonStructure([
            'message',
            'data' => [
                'sender',
                'receiver',
                'amount',
                'commission_fee',
                'total_amount_debited',
                'created_at',
            ]
        ]);

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
            'balance' => 100
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $sender->id,
            'balance' => 98.5
        ]);

        Event::assertDispatched(TransactionCompleted::class, function ($event) use ($transaction) {
            return $event->transaction->id === $transaction->id;
        });
    }
    
}
