<?php
namespace App\Repositories;

use App\Models\Grades;
use App\Repositories\Contracts\GradeRepositoryInterface;
use Illuminate\Http\Request;

class GradeRepository implements GradeRepositoryInterface{

    public function getAll(){

    }

    public function getByParam($request){
        $user_code = $request->query('user_code');
        $class_code = $request->query('class_code');
        $subject_code = $request->query('subject_code');

        $query = Grades::query();

        if($user_code){
            $query->where('user_code',$user_code);
        }

        if($class_code){
            $query->where('class_code',$class_code);
        }

        if($subject_code){
            $query->where('subject_code',$subject_code);
        }
        return $grade = $query->firstOrFail();
    }

    public function update($request , $id){
        $grade = Grades::findOrFail($id);

        return $grade->update([
            'score'=> $request->input('score')
        ]);
    }

    public function delete($id){

    }
}

