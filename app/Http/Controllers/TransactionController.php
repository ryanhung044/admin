<?php

namespace App\Http\Controllers;

use App\Models\Fee;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            // return response()->json($request->all()); // Hiển thị dữ liệu từ request
            $userCode = $request->user()->user_code;
            // $userCode = 'student05';
            if (!$userCode) {
                return response()->json('Không có user_code', 400);
            }

            // Query dữ liệu
            $transactions = Transaction::with('fee') // Lấy thông tin bảng fees
            ->whereHas('fee', function ($query) use ($userCode) {
                $query->where('user_code', $userCode);
            })
            ->orderBy('id','desc')
            ->get();
            $wallets = Wallet::where('user_code', $userCode)->get();
            return response()->json([
                'transactions' => $transactions,
                'wallets' => $wallets,
            ]);
            
        } catch (\Throwable $th) {
            return response()->json(['message' => $th], 404);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // request => { Ma hoc sinh, tien, thoi gian, bien lai }
        try {
            $fee = Fee::query()->where('user_code', $request->user_code)->firstOrFail();

            $wallet = Wallet::query()->where('user_id', $fee->user_id)->firstOrFail();

            if (!$wallet) {
                return response()->json(['message' => 'wallet not found'], 404);
            }

            $data = [
                'fee_id' => $fee->id,
                'payment_date' => $request->payment_date,
                'amount_paid' => $request->amount_paid,
                'payment_method' => $request->payment_method,
                'receipt_number' => $request->receipt_number,
            ];

            Transaction::create($data);

            $transactionsByFee = Transaction::query()
                ->where('fee_id', $fee->id)
                ->select('amount_paid')->get();

            $amountFee = 0;
            $feeAmount = (int)$fee->amount;
            foreach ($transactionsByFee as $trans) {
                $amountFee += $trans->amount_paid;
            }

            $wallet->update(['paid' => $amountFee]);

            return response()->json(['message' => 'tao transaction thanh cong']);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Transaction $transaction)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Transaction $transaction)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Transaction $transaction)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction)
    {
        //
    }
}
