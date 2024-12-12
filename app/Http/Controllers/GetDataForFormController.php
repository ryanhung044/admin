<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Subject;
use Illuminate\Http\Request;

class GetDataForFormController extends Controller
{

    // public function handleInvalidId()
    // {
    //     return response()->json([
    //         'message' => 'Không có chuyên ngành nào!',
    //     ], 404);
    // }

    //  Hàm trả về json khi lỗi không xác định (500)
    public function handleErrorNotDefine($th)
    {
        return response()->json([
            'message' => "Đã xảy ra lỗi không xác định",
            'error' => env('APP_DEBUG') ? $th->getMessage() : "Lỗi không xác định"
        ], 500);
    }

    public function listCoursesForFrom(){
        try {
            $courses = Category::where(
                [
                    'type' => 'course', 
                    'is_active' => true
                ]
            )->select('cate_code', 'cate_name')->get();
            return response()->json($courses);
        } catch (\Throwable $th) {
            return $this->handleErrorNotDefine($th);
        }
    }


    public function listSemestersForForm(){
        try {
            $semesters = Category::where([
                'is_active' => true,
                'type' => 'semester'
            ])->select('cate_code', 'cate_name')->get();
            return response()->json($semesters);
        } catch (\Throwable $th) {
            return $this->handleErrorNotDefine($th);
        }
    }

    public function listMajorsForForm(){
        try {
            $majors = Category::with([
                'childrens' => function($query){
                    $query->select('cate_code', 'cate_name', 'parent_code');
                }
            ])->where([
                'is_active' => true,
                'type' => 'major'
            ])->whereNull('parent_code')->select('cate_code', 'cate_name')->get();
            return response()->json($majors);
        } catch (\Throwable $th) {
           return $this->handleErrorNotDefine($th);
        }
    }

    public function listParentMajorsForForm(){
        try {
            $parentMajors = Category::whereNull('parent_code')->where([
                'is_active' => true,
                'type' => 'major'
            ])->select('cate_code', 'cate_name', 'is_active')->get();
            return response()->json($parentMajors);
        } catch (\Throwable $th) {
            return $this->handleErrorNotDefine($th);
        }

    }

    public function listChildrenMajorsForForm(string $parent_code){
        try {
            $childrenMajors = Category::where([
                'parent_code' => $parent_code,
                'is_active' => true,
                'type' =>'major'
            ])->select('cate_code', 'cate_name', 'is_active')->get();
            return response()->json($childrenMajors);
        } catch (\Throwable $th) {
            return $this->handleErrorNotDefine($th);   
        }

    }

    public function listSessionsForForm(){
        try {
            $sessions = Category::where([
                'type' => 'session',
                'is_active' => true
            ])->select('cate_code', 'cate_name', 'value')->get();
            return response()->json($sessions);
        } catch (\Throwable $th) {
            return $this->handleErrorNotDefine($th);
        }
    }
    public function listRoomsForForm(){
        try {
            $rooms = Category::where([
                'type' => 'school_room',
                'is_active' => true
            ])->select('cate_code', 'cate_name', 'value')->get();
            return response()->json($rooms);
        } catch (\Throwable $th) {
            return $this->handleErrorNotDefine($th);
        }
    }

    public function listSubjectsForForm(){
        try {
            $subjects = Subject::where([
                'is_active' => true
            ])->select('subject_code', 'subject_name', 'tuition', 're_study_fee', 'credit_number', 'total_sessions', 'semester_code', 'major_code')->get();
            return response()->json($subjects);
        } catch (\Throwable $th) {
            return $this->handleErrorNotDefine($th);
        }
    }

    public function listSubjectsToMajorForForm(string $major_code){
        try {
            $subjects = Subject::where([
                'is_active' => true,
                'major_code' => $major_code
            ])->select('subject_code', 'subject_name', 'tuition', 're_study_fee', 'credit_number', 'total_sessions', 'semester_code', 'major_code')->get();
            return response()->json($subjects);
        } catch (\Throwable $th) {
            return $this->handleErrorNotDefine($th);   
        }
    }
}
