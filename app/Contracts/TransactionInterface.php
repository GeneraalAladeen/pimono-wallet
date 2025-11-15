<?php

namespace App\Contracts;

interface TransactionInterface
{
    public function transferMoney($senderId, $receiverId, $amount);

    public function executeTransfer($senderId, $receiverId, $amount);

    public function processTransferSync($senderId, $receiverId, $amount);

    public function shouldProcessSync(): bool;
}
