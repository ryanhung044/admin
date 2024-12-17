<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSemesterRequest;
use App\Http\Requests\UpdateSemesterRequest;
use App\Models\Category;
use App\Models\Fee;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\User;
use App\Repositories\Contracts\SemesterRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SemesterController extends Controller
{
    protected $semesterRepository;
    public function __construct(SemesterRepositoryInterface $semesterRepository)
    {
        $this->semesterRepository = $semesterRepository;
    }
    public function index()
    {
        try {
            $model = $this->semesterRepository->getAll();
            return response()->json($model, 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()]);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSemesterRequest $request)
    {
        try {
            $data = [
                'cate_code' => 'S'.$request->value,
                'cate_name' => $request->cate_name,
                'value'     => $request->value,
                'type'      => 'semester'
            ];

            $semester =  Category::create($data);
            return response()->json(['message' => 'Thêm thành công','data'=>$semester], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    // public function update(UpdateSemesterRequest $request, int $id)
    // {
    //     try {

    //         return response()->json(['message' => 'cập nhật thành công'], 200);
    //     } catch (NotFoundHttpException $e) {
    //         return response()->json(['message' => $e->getMessage()]);
    //     } catch (\Throwable $th) {
    //         return response()->json(['message' => $th->getMessage()]);
    //     }
    // }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $cate_code)
    {
        try {
            $userExists = User::where('semester_code',$cate_code)->exists();
            $subjectExists = Subject::where('semester_code', $cate_code)->exists();
            $feeExits = Fee::where('semester_code',$cate_code)->exists();

            if($userExists || $subjectExists || $feeExits){
                return response()->json(['message'=> 'không thể xóa kỳ học'],409);
            }



            $semester = Category::where('cate_code', $cate_code)
                                ->where('type','semester')->firstOrFail();

            $semesterExist = $semester->exists();
            if($semesterExist){
                return response()->json(['message' => 'Kỳ học đã tồn tại'],409);
            }

            $semester->delete();
            return response()->json(['message' => 'xóa thành công']);
        } catch (NotFoundHttpException $e) {
            return response()->json(['message' => $e->getMessage()]);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 200);
        }
    }

    public function updateSemesterForStudent()
    {
        $students = User::where('role', '3') // Chỉ lấy sinh viên
            ->select('id', 'semester_code') // Lấy ID và kỳ học hiện tại
            ->get();

        foreach ($students as $student) {
            // Lấy value hiện tại của kỳ học
            $currentSemester = DB::table('categories')
                ->where('cate_code', $student->semester_code)
                ->select('value')
                ->first();

            if ($currentSemester) {
                // Tìm cate_code của kỳ tiếp theo (value + 1)
                $nextSemester = DB::table('categories')
                    ->where('value', $currentSemester->value + 1)
                    ->select('cate_code')
                    ->first();

                if ($nextSemester) {
                    // Cập nhật semester_code của sinh viên
                    $student->semester_code = $nextSemester->cate_code;
                    $student->save(); // Lưu thay đổi
                }
            }
        }
    }
}
