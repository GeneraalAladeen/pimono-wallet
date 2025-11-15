<?php

namespace Tests\Feature\Api\Auth;

use App\Models\Transaction;
use App\Models\User;
use Tests\TestCase;

class IndexTransactionsTest extends TestCase
{
    public function test_user_must_be_authenticated(): void
    {
        $receiver = User::factory()->create();

        $this->getJson(route('transaction.index'))->assertUnauthorized();
    }

    public function test_cannot_get_another_user_transaction(): void
    {
        $this->actingAs($user = User::factory()->create());

        $anotherUser = User::factory()->create();

        $sentTransaction = Transaction::factory(4)->for($user, 'sender')->create();
        $anotherUserTransaction = Transaction::factory()->for($anotherUser, 'sender')->create();

        $response = $this->getJson(route('transaction.index'));

        $response->assertJsonMissing([
            'sender_id' => $anotherUser->id,
        ]);

        $this->assertCount(4, $response['data']);
    }

    public function test_can_get_transactions(): void
    {
        $this->actingAs($user = User::factory()->create());

        $sentTransaction = Transaction::factory(4)->for($user, 'sender')->create();
        $receivedTransaction = Transaction::factory(4)->for($user, 'receiver')->create();

        $response = $this->getJson(route('transaction.index'));

        $response->assertSuccessful()
            ->assertJsonStructure([
                'status',
                'data' => [
                    '*' => [
                        'amount',
                        'commission_fee',
                        'total_amount_debited',
                        'created_at',
                        'sender',
                        'receiver',
                    ],
                ],
            ]);

        $this->assertCount(8, $response['data']);
    }

    public function test_transactions_can_be_paginated(): void
    {
        $this->actingAs($user = User::factory()->create());

        $sentTransaction = Transaction::factory(4)->for($user, 'sender')->create();
        $receivedTransaction = Transaction::factory(4)->for($user, 'receiver')->create();

        $response = $this->getJson(route('transaction.index', [
            'per_page' => 3,
        ]));

        $response->assertSuccessful()
            ->assertJsonStructure([
                'status',
                'data' => [
                    '*' => [
                        'amount',
                        'commission_fee',
                        'total_amount_debited',
                        'created_at',
                        'sender',
                        'receiver',
                    ],
                ],
                'links',
            ]);

        $this->assertCount(3, $response['data']);
    }
}
