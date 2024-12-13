<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Classroom;
use App\Repositories\Contracts\ScoreRepositoryInterface;

class ScoreController extends Controller{
    protected $scoreRepository;

    public function __construct(ScoreRepositoryInterface $scoreRepository){
        $this->scoreRepository = $scoreRepository;
    }
    public function getById(){
        $students = ClassRoom::all();
        return response()->json($students);
    }

    public function create($id){
        $score = $this->scoreRepository->create($id);
        return response()->json($score);
    }

    public function addStudent()
    {
        $students = [
            [
                'student_code' => 'PH12345',
            ],
            [
                'student_code' => 'PH65432',
            ],
            [
                'student_code' => 'PH12332',
            ]
        ];

        // Tìm lớp học
        $classroom = ClassRoom::findOrFail(2);

        // Cập nhật trường students với mảng JSON
        $classroom->update(['students' => $students]);

        return response()->json(['message'=>'sucess'] );
    }

    public function update(){

    }

    public function delete(){

    }
}
