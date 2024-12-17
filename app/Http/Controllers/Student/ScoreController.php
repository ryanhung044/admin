<?php

namespace App\Http\Controllers\Student;

use Throwable;
use App\Models\User;
use App\Models\Category;
use App\Models\Classroom;
use Illuminate\Http\Request;
use App\Models\ClassroomUser;
use App\Models\ScoreComponent;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class ScoreController extends Controller
{
    // Hiển thị điểm theo kỳ
    public function bangDiemTheoKy(Request $request)
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
            if($classrooms->isEmpty()){
                return response()->json(
                    ['message' => "Không có lớp học nào!"], 204
                );
            }
            return response()->json($result, 200);
        } catch (Throwable $th) {
            Log::error(__CLASS__ . '@' . __FUNCTION__, [$th]);

            return response()->json([
                'message' => 'Lỗi không xác định!'
            ], 500);
        }
    }

    //////// END

    // Hiển thị điểm tất cả các môn học của sinh viên
    public function bangDiem(Request $request)
    {
        try {
            $userCode = $request->user()->user_code;
            return $listSubject = User::where('user_code', $userCode)
                        ->with([
                            'subjectMajor' => function ($query) {
                                $query->select('subject_code', 'subject_name', 'credit_number', 'semester_code', 'major_code')
                                    ->with('semester:cate_code,cate_name');
                            },
                            'subjectNarrowMajor' => function ($query) {
                                $query->select('subject_code', 'subject_name', 'credit_number', 'semester_code', 'major_code')
                                    ->with('semester:cate_code,cate_name');
                            },
                            'scores' => function ($query) {
                                $query->select('subject_code', 'score', 'is_pass')
                                    ->with('subject:subject_code,subject_name,credit_number,semester_code,major_code')
                                    ->with(['subject.semester' => function ($query) {
                                        $query->select('cate_code', 'cate_name');
                                    }]);
                            },
                            'major' => function ($query) {
                                $query->select('cate_code', 'cate_name');
                            },
                            'narrow_major' => function ($query) {
                                $query->select('cate_code', 'cate_name');
                            }
                        ])
                        ->get(['user_code', 'major_code', 'narrow_major_code', 'semester_code']);

            $result = $listSubject->map(function ($user) {
                $userSemesterCode = $user->semester_code; 
                $subjectsMajor = $user->subjectMajor;
                $subjectsNarrowMajor = $user->subjectNarrowMajor;
                $scores = $user->scores;
                $major = $user->major->cate_name;
                $narrowMajor = $user->narrow_major->cate_name ?? null;

                // Chuyển `scores` thành danh sách `subject_code` để dễ so sánh
                $scoresMap = $scores->keyBy('subject_code');

                $finalSubjects = [];

                // Gộp tất cả các `subject` từ `subjectMajor` và `subjectNarrowMajor`
                $allSubjects = collect($subjectsMajor)->merge($subjectsNarrowMajor);

                foreach ($allSubjects as $subject) {
                    $subjectCode = $subject->subject_code;

                    if ($scoresMap->has($subjectCode)) {
                        // Nếu trùng với `scores`, lấy thông tin từ `scores`
                        $score = $scoresMap->get($subjectCode);
                        $isPass = $score->subject->semester_code === $userSemesterCode ? 'Studying' : ($score->is_pass ? 'Passed' : 'Failed');
                        $finalSubjects[] = [
                            'semester_code' => $score->subject->semester->cate_code,
                            'semester_name' => $score->subject->semester->cate_name,
                            'subject_code' => $score->subject->subject_name,
                            'subject_name' => $score->subject->subject_name,
                            'credit_number' => $score->subject->credit_number,
                            'score' => $score->score,
                            'is_pass' => $isPass,
                        ];
                    } else {
                        // Nếu không trùng, lấy thông tin từ `subjectMajor` hoặc `subjectNarrowMajor`
                        $isPass = $subject->semester_code === $userSemesterCode ? 'Studying' : 'Notyet';
                        $finalSubjects[] = [
                            'semester_code' => $subject->semester->cate_code,
                            'semester_name' => $subject->semester->cate_name,
                            'subject_code' => $subject->subject_code,
                            'subject_name' => $subject->subject_name,
                            'credit_number' => $subject->credit_number,
                            'score' => 0,
                            'is_pass' => $isPass,
                        ];
                    }
                }
                // Tính toán các thông tin
                $subjectsCollection = collect($finalSubjects);

                // Tính điểm trung bình của các môn có `is_pass` thuộc Passed, Failed, Studying
                $averageScore = $subjectsCollection
                    ->filter(fn($subject) => in_array($subject['is_pass'], ['Passed', 'Failed', 'Studying']))
                    ->avg('score');

                // Đếm tổng các môn theo từng trạng thái
                $countNotyet = $subjectsCollection->where('is_pass', 'Notyet')->count();
                $countPassed = $subjectsCollection->where('is_pass', 'Passed')->count();
                $countFailed = $subjectsCollection->where('is_pass', 'Failed')->count();
                $countStudying = $subjectsCollection->where('is_pass', 'Studying')->count();

                return [
                    'major' => $major,
                    'narrowMajor' => $narrowMajor,
                    'subjects' => $finalSubjects,
                    'averageScore' => $averageScore,
                    'countNotyet' => $countNotyet,
                    'countPassed' => $countPassed,
                    'countFailed' => $countFailed,
                    'countStudying' => $countStudying,
                ];
            });
            return response()->json($result, 200);
        } catch (Throwable $th) {
            Log::error(__CLASS__ . '@' . __FUNCTION__, [$th]);

            return response()->json([
                'message' => 'Lỗi không xác định!'
            ], 500);
        }
    }

    //////// END
    
}
