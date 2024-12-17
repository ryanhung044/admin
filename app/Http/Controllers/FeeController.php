<?php

namespace App\Http\Controllers;

use App\Models\Fee;
use App\Models\Subject;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Repositories\Contracts\FeeRepositoryInterface;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class FeeController extends Controller
{

    protected $feeRepository;

    public function __construct(FeeRepositoryInterface $feeRepository)
    {
        $this->feeRepository = $feeRepository;
    }


    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10);
            $search = $request->input('search'); // Lấy từ khóa tìm kiếm từ request
            $status = $request->input('status');
            $email = $request->input('email');
            $search = $request->input('search'); 
            $orderBy = $request->input('orderBy', 'created_at'); // Nếu không có orderBy, mặc định là 'created_at'
            $orderBy === 'user' ? ($orderBy = 'user_code') : $orderBy; 
            $orderDirection = $request->input('orderDirection', 'asc'); // Mặc định sắp xếp theo hướng 'asc'
    
            $data = $this->feeRepository->getAll($email, $status, $search, $orderBy, $orderDirection,$perPage);
            
    
            return response()->json($data);
        } catch (Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 404);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {}

    /**
     * Store a newly created resource in storage.
     */
    public function store()
    {
        // return response()->json(['data']);
        try {
            $data = $this->feeRepository->createAll();
            return response()->json(['message' => $data]);
            // return response()->json($data);
        } catch (Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 404);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            // $data = Fee::where('id', $id)->first();
            $data = Fee::find($id);
            if (!$data) {
                return response()->json([
                    'message' => 'Fee not found.',
                    'success' => false
                ], 404);
            } else {
                return response()->json([
                    $data
                ], 200);
            }
        } catch (\Throwable $th) {

            return $this->handleErrorNotDefine($th);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, $id) {}


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Fee $fee)
    {
        // return $request;
        // DB::beginTransaction();
        $fee->status = $request['status'];
        try {
            $isFullyPaid = $request['status'] == 'paid';

            if ($isFullyPaid) {
                $fee->amount = $fee->total_amount;
                // Tìm hoặc tạo ví tiền
                $wallet = Wallet::firstOrCreate(
                    ['user_code' => $fee->user_code],
                    ['total' => 0, 'paid' => 0]
                );
                $wallet->total += $isFullyPaid ? $fee->total_amount : 0;
                $wallet->paid += $fee->total_amount;
                $wallet->save();
                $transactions = [];
                $transactions[] = [
                    'fee_id' => $fee->id,
                    'payment_date' => now(),
                    'amount_paid' => $fee->total_amount,
                    'payment_method' => 'cash',
                    'receipt_number' => "",
                    'is_deposit' => 1,
                ];
                $transactions[] = [
                    'fee_id' => $fee->id,
                    'payment_date' => now(),
                    'amount_paid' => $fee->total_amount,
                    'payment_method' => 'transfer',
                    'receipt_number' => "",
                    'is_deposit' => 0,
                ];
                Transaction::insert($transactions);
            }
            $fee->save();

            return response()->json([
                'message' => 'Cập nhật học phí cho sinh viên thành công.',
                'data' => $fee,
            ], 200);
            // Logic xử lý
            // DB::commit();
        } catch (\Exception $e) {
            // DB::rollBack();
            return response()->json(['error' => 'Something went wrong!'], 500);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Fee $fee)
    {
        //
    }

    public function getListDebt(Request $request)
    {
        try {
            $userCode = $request->user()->user_code;
            if (!$userCode) {
                return response()->json('Không có user_code', 400);
            }

            $data = Fee::where('user_code', $userCode)->where('status', 'unpaid')->get();

            return response()->json($data);
        } catch (Throwable $th) {
            return response()->json(['message' => $th], 404);
        }
    }
}
