<?php

namespace App\Http\Controllers;

use Throwable;
use App\Models\Grades;
use App\Models\Subject;
use App\Models\Category;
use App\Models\Classroom;
use Illuminate\Http\Request;
use App\Models\ClassroomUser;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\UpdateGradesRequest;
use App\Repositories\Contracts\GradeRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class GradesController extends Controller
{
    // protected $gradeRepository;
    // public function __construct(GradeRepositoryInterface $gradeRepository){
    //     $this->gradeRepository = $gradeRepository;
    // }
    // public function index($classCode)
    // {
    //     try {

    //         return response()->json($classCode);
    //         // Lấy thông tin lớp học từ bảng classrooms
    //         $class = DB::table('classrooms')->where([
    //             'class_code' => $classCode,
    //             'is_active' => true
    //         ])->select('class_name', 'score', 'class_code', 'subject_code')->first();
            
    //         // Lấy danh sách sinh viên từ bảng classroom_user và nối với bảng users
    //         $listStudents = DB::table('classroom_user')
    //             ->join('users', 'classroom_user.user_code', '=', 'users.user_code')
    //             ->where('classroom_user.class_code', $classCode)
    //             ->select('users.full_name', 'classroom_user.user_code')
    //             ->get();
            
    //         // Lấy subject_code từ lớp học
    //         $subjectCode = $class->subject_code;
            
    //         // Tiến hành nối với bảng subjects để lấy assessments
    //         $subject = DB::table('subjects')
    //             ->where('subjects.subject_code', $subjectCode)
    //             ->select('subjects.subject_code', 'subjects.assessments')
    //             ->first();
            
    //         // Giải mã chuỗi assessments
    //         $assessments = json_decode($subject->assessments, true);
            
    //         // Giải mã chuỗi score nếu có
    //         $scoreArray = json_decode($class->score, true) ?? [];
    
    //         // Kiểm tra nếu scoreArray là mảng rỗng, tạo điểm mặc định
    //         if (empty($scoreArray)) {
    //             $scoreArray = []; // Khởi tạo lại scoreArray nếu rỗng
                
    //             // Lặp qua danh sách sinh viên để thêm thông tin điểm cho từng người
    //             foreach ($listStudents as $student) {
    //                 $scores = []; // Khởi tạo mảng scores cho mỗi sinh viên
                    
    //                 // Duyệt qua từng điểm trong assessments và tạo phần tử điểm cho mỗi sinh viên
    //                 foreach ($assessments as $assessmentName => $assessmentValue) {
    //                     $scores[] = [
    //                         'name' => $assessmentName, // Lấy tên điểm (ví dụ: final, midterm)
    //                         'note' => '', // Ghi chú (mặc định rỗng)
    //                         'score' => 0, // Điểm (mặc định là 0)
    //                         'value' => $assessmentValue // Lấy giá trị trọng số từ assessments
    //                     ];
    //                 }
    
    //                 // Thêm thông tin điểm của sinh viên vào mảng scoreArray
    //                 $scoreArray[] = [
    //                     'scores' => $scores, // Các điểm của sinh viên
    //                     'student_code' => $student->user_code, // Mã sinh viên
    //                     'student_name' => $student->full_name, // Tên sinh viên
    //                     'average_score' => 0 // Điểm trung bình (mặc định là 0)
    //                 ];
    //             }
    //         }
    
    //         // Trả về dữ liệu JSON cho frontend
    //         return response()->json([
    //             'classCode' => $class->class_code,
    //             'className' => $class->class_name,
    //             'score' => $scoreArray, // Điểm lớp học (dưới dạng mảng)
    //         ]);
    //     } catch (\Throwable $th) {
    //         return response()->json([
    //             'message' => 'Có lỗi xảy ra ' . $th->getMessage(),
    //             'error' => true,
    //         ]);
    //     }
    // }
    


    public function create()
    {
        //
    }
    
    // public function show(string $classCode) {

    //     try {
    //                 // Lấy thông tin lớp học từ bảng classrooms
    //                 $class = DB::table('classrooms')->where([
    //                     'class_code' => $classCode,
    //                     'is_active' => true
    //                 ])->select('class_name', 'score', 'class_code', 'subject_code')->first();
                    
    //                 // Lấy danh sách sinh viên từ bảng classroom_user và nối với bảng users
    //                 $listStudents = DB::table('classroom_user')
    //                     ->join('users', 'classroom_user.user_code', '=', 'users.user_code')
    //                     ->where('classroom_user.class_code', $classCode)
    //                     ->select('users.full_name', 'classroom_user.user_code')
    //                     ->get();
                    
    //                 // Lấy subject_code từ lớp học
    //                 $subjectCode = $class->subject_code;
                    
    //                 // Tiến hành nối với bảng subjects để lấy assessments
    //                 $subject = DB::table('subjects')
    //                     ->where('subjects.subject_code', $subjectCode)
    //                     ->select('subjects.subject_code', 'subjects.assessments')
    //                     ->first();
                    
    //                 // Giải mã chuỗi assessments
    //                 $assessments = json_decode($subject->assessments, true);
                    
    //                 // Giải mã chuỗi score nếu có
    //                 $scoreArray = json_decode($class->score, true) ?? [];
            
    //                 // Kiểm tra nếu scoreArray là mảng rỗng, tạo điểm mặc định
    //                 if (empty($scoreArray)) {
    //                     $scoreArray = []; // Khởi tạo lại scoreArray nếu rỗng
                        
    //                     // Lặp qua danh sách sinh viên để thêm thông tin điểm cho từng người
    //                     foreach ($listStudents as $student) {
    //                         $scores = []; // Khởi tạo mảng scores cho mỗi sinh viên
                            
    //                         // Duyệt qua từng điểm trong assessments và tạo phần tử điểm cho mỗi sinh viên
    //                         foreach ($assessments as $assessmentName => $assessmentValue) {
    //                             $scores[] = [
    //                                 'name' => $assessmentName, // Lấy tên điểm (ví dụ: final, midterm)
    //                                 'note' => '', // Ghi chú (mặc định rỗng)
    //                                 'score' => 0, // Điểm (mặc định là 0)
    //                                 'value' => $assessmentValue // Lấy giá trị trọng số từ assessments
    //                             ];
    //                         }
            
    //                         // Thêm thông tin điểm của sinh viên vào mảng scoreArray
    //                         $scoreArray[] = [
    //                             'scores' => $scores, // Các điểm của sinh viên
    //                             'student_code' => $student->user_code, // Mã sinh viên
    //                             'student_name' => $student->full_name, // Tên sinh viên
    //                             'average_score' => 0 // Điểm trung bình (mặc định là 0)
    //                         ];
    //                     }
    //                 }
            
    //                 // Trả về dữ liệu JSON cho frontend
    //                 return response()->json([
    //                     'classCode' => $class->class_code,
    //                     'className' => $class->class_name,
    //                     'score' => $scoreArray, // Điểm lớp học (dưới dạng mảng)
    //                 ]);
    //             } catch (\Throwable $th) {
    //                 return response()->json([
    //                     'message' => 'Có lỗi xảy ra ' . $th->getMessage(),
    //                     'error' => true,
    //                 ]);
    //             }

    // }

    public function show(string $classCode)
    {
        try {
            $classrooms = Classroom::where('class_code', $classCode)
                        ->with([
                            'subject' => function ($query) {
                                $query->with('subjectAssessment', function ($query) {
                                    $query->select('assessment_items.assessment_code', 'assessment_items.name', 'assessment_items.weight');
                                })->select('subject_code', 'subject_name', 'semester_code');
                            },
                            'users' => function ($query) {
                                $query->select('users.user_code', 'users.full_name');
                            },
                            'teacher' => function ($query) {
                                $query->select('user_code', 'full_name');
                            },
                            'scorecomponents' => function ($query) use ($classCode) {
                                $query->where('class_code', $classCode)
                                    ->with('assessmentItem', function ($query) {
                                            $query->select('assessment_code', 'name', 'weight')->orderBy('weight','asc');
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
                $teacher = $classroom->teacher ?? collect([]);
            
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
                    $sortedScores = $scores->sortByAsc('weight')->values();
                    $diem = 0;
                    $heSo = 0;

                    foreach ($sortedScores as $scoreEntry) {
                        $diem += $scoreEntry['score'] * $scoreEntry['weight'];
                        $heSo += $scoreEntry['weight'];
                    }
                    $averageScore = $heSo > 0 ? ($diem / $heSo) : 0;
                    $formattedScore = number_format($averageScore, 1, ',', '');
            
                    return [
                        'student_code' => $student->user_code,
                        'student_name' => $student->full_name,
                        'average_score' => $formattedScore,
                        'scores' => $sortedScores
                    ];
                });
            
                return [
                    'class_code' => $classroom->class_code,
                    'class_name' => $classroom->class_name,
                    'teacher_code' => $teacher->user_code,
                    'teacher_name' => $teacher->full_name,
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



