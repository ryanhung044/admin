<?php

namespace App\Http\Controllers;

use App\Models\Grades;
use App\Http\Requests\UpdateGradesRequest;
use App\Models\Subject;
use App\Models\User;
use App\Repositories\Contracts\GradeRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;
use Illuminate\Support\Facades\Auth;
use App\Models\ClassroomUser;
use App\Models\Category;
use App\Models\Classroom;

class StudentGradesController extends Controller
{
    public function index(Request $request)
    {
        try {
            $userCode = $request->user()->user_code;
            $semesterCodeUser = User::where('user_code', $userCode)
                                    ->select('semester_code')
                                    ->first();

            $semesterCode = $request->input('search') ?: $semesterCodeUser->semester_code;
        
            $listSemester = Category::where('type', 'semester')
                ->where('is_active', '1')
                ->where('cate_code', '<=', $semesterCodeUser->semester_code) 
                ->orderBy('cate_code', 'asc')
                ->select('cate_code', 'cate_name')
                ->get();
            $classrooms = Classroom::whereHas('subject', function ($query) use ($semesterCode) {
                            $query->where('semester_code', $semesterCode);
                        })
                        ->whereHas('users', function ($query) use ($userCode) {
                            $query->where('classroom_user.user_code', $userCode);
                        })
                        ->with([
                            'subject' => function ($query) {
                                $query->with('subjectAssessment', function ($query) {
                                    $query->select('assessment_items.assessment_code', 'assessment_items.name', 'assessment_items.weight');
                                })->select('subject_code', 'subject_name', 'semester_code');
                            },
                            'teacher' => function ($query) {
                                $query->select('user_code', 'full_name');
                            },
                            'scorecomponents' => function ($query) use ($userCode) {
                                $query->where('student_code', $userCode)
                                    ->with('assessmentItem', function ($query) {
                                            $query->select('assessment_code', 'name', 'weight');
                                    })
                                    ->select('student_code', 'class_code', 'score', 'assessment_code');
                            }

                        ])
                        ->get(['class_code', 'class_name', 'subject_code', 'user_code']);
            $result = $classrooms->map(function ($classroom) {
                $subjectAssessments = $classroom->subject->subjectAssessment ?? collect([]);
                $scoreComponents = $classroom->scorecomponents ?? collect([]);
                
                // Duyệt qua các subjectAssessment để xử lý dữ liệu
                $scores = $subjectAssessments->map(function ($assessment) use ($scoreComponents) {
                    $matchedScore = $scoreComponents->firstWhere('assessment_code', $assessment->assessment_code);
            
                    return [
                        'assessment_name' => $matchedScore->assessmentItem->name ?? $assessment->name,
                        'weight' => $matchedScore->assessmentItem->weight ?? $assessment->weight,
                        'score' => $matchedScore->score ?? 0 // Nếu không có điểm, trả về 0
                    ];
                });
                $diem = 0;
                $heSo = 0;
                foreach ($scores as $scoreEntry) {
                    $diem += $scoreEntry['score'] * $scoreEntry['weight'];
                    $heSo += $scoreEntry['weight'];
                }
                $averageScore = $heSo > 0 ? ($diem / $heSo) : 0;
                $formattedScore = number_format($averageScore, 1, ',', '');
            
                return [
                    'class_code' => $classroom->class_code,
                    'class_name' => $classroom->class_name,
                    'average_score' => $formattedScore,
                    'scores' => $scores
                ];
            });
            return response()->json([
                'scores' => $result,
                'semesters' => $listSemester,
                'semesterCode' => $semesterCode,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Có lỗi xảy ra: ' . $th->getMessage(),
                'error' => true,
            ]);
        }
    }



    public function create()
    {
        //
    }

    public function show(Grades $grades) {}


    
    // public function getByParam(Request $request){
    //     try{
    //         $grade = $this->gradeRepository->getByParam($request);

    //         return response()->json($grade);
    //     }catch(ModelNotFoundException $e){
    //         return response()->json(['message'=>'không tìm thấy bản ghi'],404);
    //     }
    //     catch(\Throwable $th){
    //         return response()->json(['error'=>$th->getMessage()],500);
    //     }
    // }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Grades  $grades
     * @return \Illuminate\Http\Response
     */

    //  public function update(UpdateGradesRequest $request, $classCode)
    //  {

    //  }





}
