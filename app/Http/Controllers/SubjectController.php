<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Http\Requests\StoreSubjectRequest;
use App\Http\Requests\UpdateSubjectRequest;
use App\Models\Classroom;
use App\Repositories\Contracts\SubjectRepositoryInterface;
use App\Repositories\SubjectRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubjectController extends Controller
{
    protected $subjectRepository;

    public function __construct(SubjectRepositoryInterface $subjectRepository)
    {
        $this->subjectRepository = $subjectRepository;
    }


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
            $perPage = $request->input('per_page', 10);
            $search = $request->input('search'); // Lấy từ khóa tìm kiếm từ request

            $subjects = Subject::select('subject_code', 'subject_name', 'major_code', 'is_active')
                ->with([
                    'major' => function ($query) {
                        $query->select('cate_code', 'cate_name');
                    }
                ])
                ->when($search, function ($query, $search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('subject_code', 'like', "%{$search}%")
                        ->orWhere('subject_name', 'like', "%{$search}%")
                        ->orWhere('major_code', 'like', "%{$search}%");
                    });
                })
                ->paginate($perPage);

            return response()->json([
                'status' => true,
                'subjects' => $subjects
            ], 200);
        } catch (\Throwable $th) {
            return $this->handleErrorNotDefine($th);
        }
    }   

    public function store(StoreSubjectRequest $request)
    {
        try {
            // Lấy mã môn học mới nhất và tạo subject_code mới
            $newestSubjectCode = Subject::withTrashed()
                ->where('major_code', 'LIKE', $request['major_code'])
                ->selectRaw("MAX(CAST(SUBSTRING(subject_code, 4) AS UNSIGNED)) as max_number")
                ->value('max_number');
            $nextNumber = $newestSubjectCode ? $newestSubjectCode + 1 : 1;

            $newSubjectCode = $request['major_code'] . str_pad($nextNumber, 2, '0', STR_PAD_LEFT);
            $request['subject_code'] = $newSubjectCode;

            // Tạo bản ghi mới trong bảng subjects
            $subject = $this->subjectRepository->create($request);

            // Lấy danh sách assessment_items từ request
            $assessmentItems = $request['assessment_items'];

            // Thêm các đầu điểm vào bảng subject_assessment
            $subjectAssessments = [];
            foreach ($assessmentItems as $assessmentCode) {
                $subjectAssessments[] = [
                    'subject_code' => $newSubjectCode,
                    'assessment_code' => $assessmentCode,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            return $subjectAssessments;
            // Chèn vào bảng subject_assessment
            if (!empty($subjectAssessments)) {
                DB::table('subject_assessment')->insert($subjectAssessments);
            }

            return response()->json(['message' => 'Thêm mới thành công'], 201);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 400);
        }
    }



    public function show(string $subject_code)
    {
        try {
            $subject =  Subject::select('subject_code', 'subject_name', 'tuition', 're_study_fee', 'credit_number', 'total_sessions', 'description', 'major_code', 'is_active', 'semester_code')->with([
                'semester' => function ($query) {
                    $query->select('cate_code', 'cate_name');
                },
                'major' => function ($query) {
                    $query->select('cate_code', 'cate_name');
                }
            ])->firstWhere('subject_code', $subject_code);

            if (!$subject) {
                return response()->json([
                    'status' => false,
                    'message' => 'Môn học này không tồn tại!'
                ], 404);
            }


            $semester_info = optional($subject->semester);
            $major_info = optional($subject->major);
            return response()->json(

                [
                    'status' => true,
                    'subject' => [
                        'subject_code' => $subject->subject_code,
                        'subject_name' => $subject->subject_name,
                        'tuition' => $subject->tuition,
                        're_study_fee' => $subject->re_study_fee,
                        'credit_number' => $subject->credit_number,
                        'total_sessions' => $subject->total_sessions,
                        'description' => $subject->description,
                        'is_active' => $subject->is_active,
                        'semester_code' => $semester_info->cate_code,
                        'semester_name' => $semester_info->cate_name,
                        'major_code' => $major_info->cate_code,
                        'major_name' => $major_info->cate_name
                    ]
                ],
                200
            );
        } catch (\Throwable $th) {
            return $this->handleErrorNotDefine($th);
        }
    }

    public function update(UpdateSubjectRequest $request, string $subject_code)
    {
        try {
            DB::beginTransaction();
            $data = $request->validated();

            $subject = Subject::where('subject_code', $subject_code)->lockForUpdate()->first();

            if (!$subject) {
                return response()->json([
                    'status' => false,
                    'message' => 'Môn học này không tồn tại!'
                ], 404);
            }

            $is_studying = Classroom::where('subject_code', $subject_code)->exists();

            // if ($is_studying) {
            //     return response()->json([
            //         'status' => false,
            //         'message' => 'Bạn không thể sửa môn học này vì có lớp đang học!'
            //     ], 409);
            // }


            $subject->update($data);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Cập nhật thông tin môn học thành công!'
            ]);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 400);
        }
    }

    public function destroy(string $subject_code)
    {
        try {
            DB::beginTransaction();
            $subject = Subject::where('subject_code', $subject_code)->lockForUpdate()->first();

            if (!$subject) {
                return response()->json([
                    'status' => false,
                    'message' => 'Môn học này không tồn tại!'
                ]);
            }

            $is_studying = Classroom::where('subject_code', $subject_code)->exists();

            if ($is_studying) {
                return response()->json([
                    'status' => false,
                    'message' => 'Bạn không thể xoá môn học này vì có lớp đang học!'
                ], 409);
            }

            $subject->delete();

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Xóa môn học thành công'
            ], 200);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['message' => 'Đã có lỗi xảy ra: ' . $th->getMessage()], 400);
        }
    }

    public function renderSubjectForClassroom() {}
}
