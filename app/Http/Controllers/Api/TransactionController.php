<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\SystemOverloadException;
use App\Http\Controllers\Controller;
use App\Jobs\Transaction\ProcessTransfer;
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
            $result = $this->transactionService->transferMoney(
                $request->user()->id,
                $request->receiver_id,
                $request->amount
            );

            if (isset($result['status']) && $result['status'] === 'queued') {
                return response()->json($result, 202);
            }

            return response()->json([
                'message' => 'Transfer completed successfully',
                'data' => TransactionResource::make($result),
            ], 201);

        } catch (SystemOverloadException $e) {

            ProcessTransfer::dispatch(
                $request->user()->id,
                $request->receiver_id,
                $request->amount
            );

            return response()->json([
                'message' => 'System busy. Transfer queued for processing.',
                'status' => 'queued',
            ], 202);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error processing transfer',
            ], 400);
        }
    }
}
