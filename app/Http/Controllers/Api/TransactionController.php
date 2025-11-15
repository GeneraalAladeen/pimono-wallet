<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Transaction\TransferRequest;
use App\Services\TransactionService;
use App\Http\Resources\TransactionResource;
use App\Http\Controllers\Controller;



class TransactionController extends Controller
{
    public function __construct(public TransactionService $transactionService)
    {
        //
    }

    public function store(TransferRequest $request)
    {
        try {
            $result = $this->transactionService->executeTransfer(
                $request->user()->id,
                $request->receiver_id,
                $request->amount
            );

            return response()->json([
                'message' => 'Transfer completed successfully',
                'data' => TransactionResource::make($result)
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }
 
}
