<?php

namespace App\Http\Controllers;

use App\Models\Grades;
use App\Http\Requests\UpdateGradesRequest;
use App\Models\Classroom;
use App\Models\Subject;
use App\Repositories\Contracts\GradeRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;
use Illuminate\Support\Facades\Auth;

class TeacherGradesController extends Controller
{
    public function index(Request $request, string $classCode)
    {
        try {
            $userCode = $request->user()->user_code;
            $classrooms = Classroom::where('class_code', $classCode)->where('user_code', $userCode)
                        ->with([
                            'subject' => function ($query) {
                                $query->with('subjectAssessment', function ($query) {
                                    $query->select('assessment_items.assessment_code', 'assessment_items.name', 'assessment_items.weight');
                                })->select('subject_code', 'subject_name', 'semester_code');
                            },
                            'users' => function ($query) {
                                $query->select('users.user_code', 'users.full_name');
                            },
                            'scorecomponents' => function ($query) use ($classCode) {
                                $query->where('class_code', $classCode)
                                    ->with('assessmentItem', function ($query) {
                                            $query->select('assessment_code', 'name', 'weight');
                                    })
                                    ->select('student_code', 'class_code', 'score', 'assessment_code');
                            }
                        ])
                        ->get(['class_code', 'class_name', 'subject_code', 'user_code']);
                        // dd($classrooms);
            $result = $classrooms->map(function ($classroom) {
                $subjectAssessments = $classroom->subject->subjectAssessment ?? collect([]);
                $scoreComponents = $classroom->scorecomponents ?? collect([]);
                $students = $classroom->users ?? collect([]);
            
                // Xử lý điểm cho từng sinh viên
                $studentsWithScores = $students->map(function ($student) use ($subjectAssessments, $scoreComponents) {
                    $scores = $subjectAssessments->map(function ($assessment) use ($scoreComponents, $student) {
                        $matchedScore = $scoreComponents
                            ->first(function ($score) use ($assessment, $student) {
                                return $score->assessment_code === $assessment->assessment_code &&
                                        $score->student_code === $student->user_code;
                            });
            
                        return [
                            'assessment_code' => $matchedScore->assessmentItem->assessment_code ?? $assessment->assessment_code,
                            'assessment_name' => $matchedScore->assessmentItem->name ?? $assessment->name,
                            'weight' => $matchedScore->assessmentItem->weight ?? $assessment->weight,
                            'score' => $matchedScore->score ?? 0 // Điểm mặc định là 0 nếu không có
                        ];
                    });
            
                    // Tính điểm trung bình
                    $diem = 0;
                    $heSo = 0;
                    foreach ($scores as $scoreEntry) {
                        $diem += $scoreEntry['score'] * $scoreEntry['weight'];
                        $heSo += $scoreEntry['weight'];
                    }
                    $averageScore = $heSo > 0 ? ($diem / $heSo) : 0;
                    $formattedScore = number_format($averageScore, 1, ',', '');
            
                    return [
                        'student_code' => $student->user_code,
                        'student_name' => $student->full_name,
                        'average_score' => $formattedScore,
                        'scores' => $scores
                    ];
                });
            
                return [
                    'class_code' => $classroom->class_code,
                    'class_name' => $classroom->class_name,
                    'students' => $studentsWithScores
                ];
            });
            if($classrooms->isEmpty()){
                return response()->json(
                    ['message' => "Không có lớp học nào!"], 204
                );
            }
            return response()->json($result, 200);            
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Có lỗi xảy ra' . $th->getMessage(),
                'error' => true,
            ]);
        }
    }
    public function getTeacherClass()
    {
        try {
            $user = Auth::user(); 
            $teacher_code = $user->user_code;
            // $teacher_code = 'TC969';
            $classrooms = Classroom::with([
                'subject' => function($query){
                    $query->select('subject_code', 'subject_name');
                
                }])->select('class_code', 'class_name', 'description', 'is_active', 'subject_code', 'user_code')->where('user_code', $teacher_code)->get();

            if($classrooms->isEmpty()){
                return response()->json(
                    ['message' => "Không có lớp học nào!"], 204
                );
            }
            return response()->json($classrooms,200);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Có lỗi xảy ra',
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

    public function update(UpdateGradesRequest $request, string $classCode)
    {
        try {
            $userCode = $request->user()->user_code;
            $classroom = Classroom::where('user_code', $userCode)->where('class_code', $classCode)->first();
            if (!$classroom) {

                return response()->json([
                    'message' => 'Bạn không có quyền thêm điểm vào lớp này'
                ], 200);
            }

            $studentsData = $request->all(); 
            $students = $studentsData['students'] ?? [];
    
            foreach ($students as $student) {
                $studentCode = $student['student_code'];
                $scores = $student['scores'] ?? [];
    
                foreach ($scores as $score) {
                    $assessmentCode = $score['assessment_code'] ?? null; // Lấy mã bài kiểm tra
                    $scoreValue = $score['score'] ?? null; // Lấy điểm
    
                    if (!$assessmentCode) {
                        throw new \Exception("Thiếu mã bài kiểm tra cho sinh viên {$studentCode}");
                    }
    
                    // Thêm mới hoặc cập nhật vào bảng `scores_component`
                    DB::table('scores_component')->updateOrInsert(
                        [
                            'class_code' => $classCode,
                            'student_code' => $studentCode,
                            'assessment_code' => $assessmentCode,
                        ],
                        [
                            'score' => $scoreValue,
                            'updated_at' => now(),
                        ]
                    );
                }
            }
    
            return response()->json([
                'message' => 'Cập nhật điểm thành công',
                'error' => false,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Có lỗi xảy ra: ' . $th->getMessage(),
                'error' => true,
            ], 500);
        }
    }
     
     


}
