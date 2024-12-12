<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\ClassroomUser;
use App\Models\User;

class StudentController extends Controller
{
    public function listStudentForClassroom(string $class_code){
        try {
            $teacher_code = request()->user()->user_code;
            $classroom = Classroom::where([
                'class_code' => $class_code, 
                'user_code' => $teacher_code
                ])
                ->select('class_code')->first();

            if(!$classroom){
                return response()->json([
                    'message' => 'Không tìm thấy lớp học!'], 404
                );
            }

            $student_codes = ClassroomUser::where('class_code', $classroom->class_code)->pluck('user_code');
            
            if($student_codes->isEmpty()){
                return response()->json('Không có sinh viên nào!',404);
            }
            $list_students = User::whereIn('user_code', $student_codes)->get();
            return response()->json($list_students);
        } catch (\Throwable $th) {
            return $this->handleErrorNotDefine($th);
        }
    }
}
