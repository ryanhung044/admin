<?php
namespace App\Repositories;

use App\Models\Classroom;
use App\Models\Subject;
use App\Repositories\Contracts\ScoreRepositoryInterface;

class ScoreRepository implements ScoreRepositoryInterface{
    public function getById($id){
        $scores = Classroom::where('id',$id)->pluck('score');
        return $scores;
    }

    public function create($id){
        $classroom = Classroom::find($id);
        $subject = $classroom->subject;
        $assessmentItem = $subject->assessmentItem;

        $students = Classroom::where('id', $id)->pluck('students')->first();

         if (!$students) {
            return response()->json(['message' => 'No students found'], 404);
        }

        foreach ($students as $student) {
                $list[]=[
                    'student_code' => $students,
                    'assessment_item' => $assessmentItem
                ];
            }
         return $assessmentItem;
    }

    public function addStudent(){

    }

    public function update(){

    }

    public function delete(){

    }
}
