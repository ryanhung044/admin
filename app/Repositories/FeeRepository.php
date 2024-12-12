<?php
namespace App\Repositories;

use App\Models\Fee;
use App\Models\Subject;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Repositories\Contracts\FeeRepositoryInterface;
use App\Jobs\SendEmailJob;
use App\Models\Category;
use Illuminate\Database\QueryException;

class FeeRepository implements FeeRepositoryInterface {
    public function getAll($email = null ,$status = null){
        $data =  Fee::query()->with(['user' => function($query){
            $query->select('id', 'user_code', 'full_name', 'email', 'phone_number');
        }]);

        if($status){
            $data->where('status',$status);
        }

        if($email){
            $data->whereHas('user', function($query) use ($email) {
                $query->where('email', 'like', '%' . $email.'%' );
            });

        }

        return $data->paginate(20);
    }

    public function createAll(){

        Transaction::query()->delete();
        Fee::query()->delete();
        $message = [];
        $students = User::with(['semester' => function ($query) {
            $query->select('cate_code', 'value'); // chỉ lấy cate_code và name từ bảng categories
        }])
        ->where('role', "3")
        ->select('id', 'full_name','user_code','semester_code')
        ->get();

        //  return $students;
        foreach ($students as $stu) {

            $nextSemester = $stu->semester->value + 1;
            if($nextSemester == 8){
                continue;
            }

            $semesterCode = 'S'.$nextSemester;
            $subjects = Subject::whereHas('semester', function ($query) use ($nextSemester) {
                $query->where('value', $nextSemester);
            })
            ->with(['semester' => function ($query) {
                $query->select('cate_code', 'value', 'id');
            }])
            ->get();

            // return $subjects;

            $totalAmount = $subjects->sum('tuition');

            $feeData  = [
                'user_code' => $stu->user_code,
                'total_amount' => $totalAmount,
                'semester_code' => $semesterCode,
                'amount'  => 0,
                'start_date' => '2024-10-01',
                'due_date' => '2024-10-31',
                'status'  => 'unpaid'
            ];

            // try{
            //     $fee = Fee::create($feeData);
            //     $message[] = "Tạo fee thành công";
            // }catch(QueryException $e){
            //     if ($e->getCode() === '23000') {
            //         $message[] = "Lỗi trùng lặp";
            //         continue;
            //     }
            // }
            try {
                $existingFee = Fee::where('user_code', $stu->user_code)
                    ->where('semester_code', $semesterCode)
                    ->first();
            
                if ($existingFee) {
                    $message[] = "Fee đã tồn tại cho user_code: {$stu->user_code} và semester_code: {$semesterCode}";
                    continue; // Bỏ qua nếu đã tồn tại
                }
            
                $fee = Fee::create($feeData);
                $message[] = "Tạo fee thành công";
            } catch (QueryException $e) {
                $message[] = "Lỗi không xác định: {$e->getMessage()}";
            }
            


        //     // $totalFees = Fee::where('user_id', $fee->user_id)->sum('amount');
        //     //  Wallet::query()
        //     // ->where('user_id',$fee->user_id)
        //     // ->update(['total'=>$totalFees]);
        }

        return $message;
    }

    public function sendEmailToUsers (array $user = []){

        if (empty($user)) {
            dd('No users provided for email sending');
        }

        foreach ($user as $email) {
            $data = ['email' => $email];

            // Dispatch job để gửi email
            dispatch(new SendEmailJob($data));
        }

        dd('Email Send Successfully');
    }
}
