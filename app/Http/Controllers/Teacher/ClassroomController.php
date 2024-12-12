<?php

namespace App\Http\Controllers\Teacher;

use App\Models\User;
use App\Models\Classroom;
use App\Exports\ScoreExport;
use App\Imports\ScoreImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use DateTime;
use Maatwebsite\Excel\Facades\Excel;


class ClassroomController extends Controller
{
    /**
     * Display a listing of the resource.
     */


     public function handleInvalidId()
     {
        return response()->json([
            'message' => 'Lớp học không tồn tại!',
        ], 200);
     }
 
     //  Hàm trả về json khi lỗi không xác định (500)
     public function handleErrorNotDefine($th)
     {
        return response()->json([
            'message' => "Đã xảy ra lỗi không xác định",
            'error' => env('APP_DEBUG') ? $th->getMessage() : "Lỗi không xác định"
        ], 500);
     }


    public function index(Request $request)
    {
        try {
            $teacher_code = request()->user()->user_code;
            $classrooms = Classroom::where('user_code', $teacher_code)->where('is_active', true)
                            ->orderBy('id', 'DESC')
                            ->with(['subject' => function($query){
                                $query->select('subject_code', 'subject_name');

                            },
                            'teacher' => function($query) {
                                $query->select('user_code', 'full_name');
                                
                            },
                            'users' => function($query) {
                                $query->select('users.user_code', 'users.full_name');
                                
                            },
                            'schedules.room' => function($query) {
                                $query->select('cate_code', 'cate_name', 'value');
                                
                            },
                            'schedules.session' => function($query) {
                                $query->select('cate_code', 'cate_name', 'value');
                                
                            }])->get(['class_code', 'class_name', 'user_code', 'is_active', 'subject_code']);
            $result = $classrooms->map(function($classroom) {
                $schedules_first = $classroom->schedules->first();
                $student = $classroom->users;
                $totalStudent = $student->count();
                $studyTime = json_decode($schedules_first->session['value'], true);
                return [
                    'class_code' => $classroom->class_code ?? null,
                    'class_name' => $classroom->class_name ?? null,
                    'subject_name' => $classroom->subject->subject_name ?? null,
                    'teacher_name' => $classroom->teacher->full_name ?? null,
                    'type_day' => (new DateTime($schedules_first->date))->format('d') % 2 != 0 ? 'Thứ 2,4,6' : 'Thứ 3,5,7',
                    'total_student' => $totalStudent ?? null,
                    'room_name' => $schedules_first->room->cate_name ?? null,
                    'session_name' => $schedules_first->session->cate_name ?? null,
                    'value' => $studyTime ?? null,                    
                ];
            });
            if($classrooms->isEmpty()){
                return response()->json(
                    ['message' => "Không có lớp học nào!"], 204
                );
            }
            return response()->json($result,200);

        } catch (\Throwable $th) {
            return $this->handleErrorNotDefine($th);
        }
      
    }

    public function show(Request $request, string $classcode)
    {
        try {
            $teacher_code = request()->user()->user_code;

            $classroom = Classroom::with( [
                'subject' => function($query){
                    $query->select('subject_code', 'subject_name', 'credit_number', 'total_sessions', 'description', 'semester_code', 'major_code' );
                },
                'subject.semester' => function($query){
                    $query->select('id','cate_code', 'cate_name');
                },
                'subject.major' => function($query){
                    $query->select('id','cate_code', 'cate_name');
                },
            ])->firstWhere([
                'class_code'=> $classcode,
            ]);

            if(!$classroom){
                return $this->handleInvalidId();
            }

            if($classroom->user_code !== $teacher_code){
                return response()->json('Bạn không dạy lớp học này', 403);
            }


            return response()->json($classroom,200);
        } catch (\Throwable $th) {
            return $this->handleErrorNotDefine($th);
        }
       
    }

    // public function listStudents(Request $request, string $classcode){
    //     try {
    //         $teacher_code = request()->user()->user_code;
    //         $classroom = Classroom::where([
    //             'class_code' => $classcode, 
    //             'user_code' => $teacher_code
    //             ])
    //             ->select('class_code')->first();

    //         if(!$classroom){
    //             return response()->json([
    //                 'message' => 'Không tìm thấy lớp học nào!'], 404
    //             );
    //         }


    //         $student_codes = DB::table('classroom_user')
    //         ->where('class_code', $classroom->class_code)->pluck('user_code');
            
    //         if($student_codes->isEmpty()){
    //             return response()->json('Không có sinh viên nào!',404);
    //         }
    //         $list_students = User::whereIn('user_code', $student_codes)->get();
    //         return response()->json($list_students);
    //     } catch (\Throwable $th) {
    //         return $this->handleErrorNotDefine($th);
    //     }
    // }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    public function exportScore(string $classCode)
    {
        $listClassroom = Classroom::where('class_code', $classCode)
                        ->get(['class_code', 'class_name', 'score'])
                        ->map(function($classroom) {
                            $score = json_decode($classroom['score'], true);
                            return [
                                'class_code' => $classroom->class_code,
                                'class_name' => $classroom->class_name,
                                'score' => $score,
                            ];
                        });
        return Excel::download(new ScoreExport($listClassroom), 'bang_diem.xlsx');
    }

    public function importScore(Request $request)
    {
        // Kiểm tra file upload
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        // Lấy mã lớp từ tiêu đề
        $classCode = $this->extractClassCodeFromFile($request->file('file'));

        if (!$classCode) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xác định mã lớp từ file!',
            ]);
        }

        // Thực hiện import dữ liệu
        Excel::import(new ScoreImport($classCode), $request->file('file'));

        return response()->json([
            'class_code' => $classCode,
            'success' => true,
            'message' => 'Import dữ liệu thành công!',
        ]);
    }

    /**
     * Trích xuất mã lớp từ tiêu đề file Excel
     */
    private function extractClassCodeFromFile($file)
    {
        $data = Excel::toArray([], $file);
       
        $firstRow = $data[0][0][0] ?? null;

        if (is_array($firstRow)) {
            $firstRow = implode(' ', $firstRow);
        }

        if ($firstRow) {
            preg_match('/lớp\s(.+?)$/i', $firstRow, $matches);
            return $matches[1] ?? null;
        }

        return null;
    }
}
