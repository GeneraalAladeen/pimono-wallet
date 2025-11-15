<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Transaction\QueryTransactionRequest;
use App\Http\Requests\Transaction\TransferRequest;
use App\Http\Resources\TransactionResource;
use App\Services\TransactionService;

class TransactionController extends Controller
{
    public function __construct(public TransactionService $transactionService)
    {
        //
    }

    public function index(QueryTransactionRequest $request)
    {
        $transactions = $this->transactionService->getUserTransactions($request->user(), $request->query('per_page') ?? 10);

        return response()->json(array_merge(
            ['status' => 'sucess'],
            TransactionResource::collection($transactions)->response()->getData(true)
        ));
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
                'data' => TransactionResource::make($result),
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
