<?php

namespace App\Http\Controllers\Api;

use Throwable;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Models\Classroom;
use App\Models\ClassroomUser;
use App\Models\Schedule;
use App\Models\Score;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Date;
use PHPUnit\Framework\Constraint\Count;

class CategoryController extends Controller
{
    // CRUD chuyên mục

    // Hàm trả về json khi id không hợp lệ
    public function handleInvalidId()
    {

        return response()->json([
            'message' => 'Không có chuyên mục nào!',
        ], 404);
    }

    //  Hàm trả về json khi lỗi không xác định (500)
    public function handleErrorNotDefine($th)
    {
        Log::error(__CLASS__ . '@' . __FUNCTION__, [$th]);

        return response()->json([
            'message' => 'Lỗi không xác định!'
        ], 500);
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {

            $search = $request->input('search');
            $categories = Category::with(['childrens' => function ($query) {
                $query->select('cate_code', 'cate_name', 'is_active');
            }])->select('cate_code', 'cate_name', 'image', 'parent_code', 'is_active')
                ->whereNull('parent_code')
                ->where('type', '=', 'category')
                ->when($search, function ($query, $search) {
                    return $query
                        ->where('cate_name', 'like', "%{$search}%")
                        ->orWhereHas('childrens', function ($childrenQuery) use ($search) {
                            return $childrenQuery->where('cate_name', 'like', "%$search%");
                        });
                })
                ->paginate(4);

            //             // Tìm kiếm theo cate_name
            //             $search = $request->input('search');
            //             // $data = Category::with([
            //             //     'childrens' => query
            //             // ]
            //             // )->where('type', '=', 'category')
            //             //     ->when($search, function ($query, $search) {
            //             //         return $query
            //             //             ->where('cate_name', 'like', "%{$search}%");
            //             //     })
            //             //     ->paginate(4);


            //                 $categories = Category::with(
            //                     ['childrens' => function ($query) {
            //     $query->select('cate_code', 'cate_name', 'parent_code', 'is_active');
            //                 }])
            //                 ->whereNull('parent_code')
            //                 ->where('type', '=', 'major')
            //                 ->select('cate_code', 'cate_name', 'is_active')
            //                 ->when($search, function($query, $search){
            //                         return $query->where('cate_name', 'like', "%$search%")->orWhereHas("childrens", function($childQuerry) use ($search){
            //                             $childQuerry->where('cate_name', 'like', "%$search%");
            //                         });
            //                     })
            //                     ->get();


            // Tìm kiếm theo cate_name
            $search = $request->input('search');
            // $data = Category::with([
            //     'childrens' => query
            // ]
            // )->where('type', '=', 'category')
            //     ->when($search, function ($query, $search) {
            //         return $query
            //             ->where('cate_name', 'like', "%{$search}%");
            //     })
            //     ->paginate(4);

            return response()->json($categories, 200);
        } catch (Throwable $th) {
            return $this->handleErrorNotDefine($th);
        }
    }

    public function listParentCategories()
    {
        try {
            $parent_category = Category::select('cate_code', 'cate_name')
                ->where([
                    'type' =>  'category',
                    'is_active' => true
                ])->whereNull('parent_code')->get();

            return response()->json($parent_category);
        } catch (\Throwable $th) {
            return $this->handleErrorNotDefine($th);
        }
    }

    public function listChildrenCategories(string $parent_code)
    {
        try {
            $children_categories = Category::where([
                'parent_code' => $parent_code,
                'type' => 'category',
                'is_active' => true
            ])->select('cate_code', 'cate_name')->get();
            return response()->json($children_categories, 200);
        } catch (\Throwable $th) {
            return $this->handleErrorNotDefine($th);
        }
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoryRequest $request)
    {
        try {
            // Lấy ra cate_code và cate_name của cha
            $parent = Category::whereNull('parent_code')
                ->where('type', '=', 'category')
                ->select('cate_code', 'cate_name')
                ->get();

            $params = $request->except('_token');
            if ($request->hasFile('image')) {
                $fileName = $request->file('image')->store('uploads/image', 'public');
            } else {
                $fileName = null;
            }

            $params['image'] = $fileName;
            Category::create($params);

            return response()->json($params, 200);
        } catch (Throwable $th) {

            return $this->handleErrorNotDefine($th);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(string $cate_code)
    {
        try {
            $category = Category::where('cate_code', $cate_code)->first();
            if (!$category) {

                return $this->handleInvalidId();
            } else {

                return response()->json($category, 200);
            }
        } catch (\Throwable $th) {

            return $this->handleErrorNotDefine($th);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryRequest $request, string $cate_code)
    {
        try {
            // Lấy ra cate_code và cate_name của cha
            $parent = Category::whereNull('parent_code')
                ->where('type', '=', 'Category')
                ->select('cate_code', 'cate_name')
                ->get();

            $listCategory = Category::where('cate_code', $cate_code)->first();
            if (!$listCategory) {

                return $this->handleInvalidId();
            } else {
                $params = $request->except('_token', '_method');
                if ($request->hasFile('image')) {
                    if ($listCategory->image && Storage::disk('public')->exists($listCategory->image)) {
                        Storage::disk('public')->delete($listCategory->image);
                    }
                    $fileName = $request->file('image')->store('uploads/image', 'public');
                } else {
                    $fileName = $listCategory->image;
                }
                $params['image'] = $fileName;
                $listCategory->update($params);

                return response()->json($listCategory, 201);
            }
        } catch (Throwable $th) {

            return $this->handleErrorNotDefine($th);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $cate_code)
    {
        try {
            $listCategory = Category::where('cate_code', $cate_code)->first();
            if (!$listCategory) {

                return $this->handleInvalidId();
            } else {
                if ($listCategory->image && Storage::disk('public')->exists($listCategory->image)) {
                    Storage::disk('public')->delete($listCategory->image);
                }
                $listCategory->delete($listCategory);

                return response()->json([
                    'message' => 'Xóa thành công'
                ], 200);
            }
        } catch (Throwable $th) {

            return $this->handleErrorNotDefine($th);
        }
    }

    public function bulkUpdateType(Request $request)
    {
        try {
            $activies = $request->input('is_active'); // Lấy dữ liệu từ request            
            foreach ($activies as $cate_code => $active) {
                // Tìm category theo ID và cập nhật trường is_active
                $category = Category::findOrFail($cate_code);
                $category->ia_active = $active;
                $category->save();
            }

            return response()->json([
                'message' => 'Trạng thái đã được cập nhật thành công!'
            ], 200);
        } catch (\Throwable $th) {

            return $this->handleErrorNotDefine($th);
        }
    }

    // END CRUD chuyên mục

    public function updateActive(string $code)
    {
        try {
            $listCategory = Category::where('cate_code', $code)->firstOrFail();
            // dd(!$listCategory->is_active);
            $listCategory->update([
                'is_active' => !$listCategory->is_active
            ]);
            $listCategory->save();
            return response()->json([
                'message' => 'Cập nhật thành công',
                'error' => false
            ], 200);
        } catch (\Throwable $th) {
            Log::error(__CLASS__ . '@' . __FUNCTION__, [$th]);

            return response()->json([
                'message' => 'Lỗi không xác định',
                'error' => true
            ], 500);
        }
    }

    public function getAllCategory(string $type)
    {
        // dd($type);
        $data = DB::table('categories')->where('type', '=', $type)->get();
        return response()->json($data);
    }

    public function getListCategory(string $type)
    {
        // Lấy tất cả danh mục cha
        // dd($type);
        $categories = DB::table('categories')
            ->where('type', '=', $type)
            ->where('parent_code', '=', null)
            ->get();
        // dd($categories);
        $data = $categories->map(function ($category) {
            // Lấy danh mục con dựa trên parent_code
            $subCategories = DB::table('categories')
                ->where('parent_code', '=', $category->cate_code)
                ->get();
            // Trả về cấu trúc dữ liệu theo yêu cầu
            return [
                'id' => $category->id,
                'cate_code' => $category->cate_code,
                'cate_name' => $category->cate_name,
                'image' => $category->image,
                'description' => $category->description,
                'listItem'  => $subCategories
            ];
        });
        return response()->json($data);
    }

    public function uploadImage(Request $request)
    {
        if ($request->hasFile('image')) {
            // Xử lý tên file
            $fileName = $request->file('image')->store('uploads/image', 'public');
            return response()->json([
                'error' => false,
                'url' => Storage::url($fileName),  // Trả về URL chính xác
                'message' => 'Upload success'
            ], 200);
        }

        return response()->json([
            'error' => true,
            'url' => null,
            'message' => 'Upload failed'
        ], 400);
    }



    public function automaticClassroom()
    {
        $listHocSinh = $this->generateStudentList();
        $listPhonghoc = $this->generateClassrooms();
        $listMonHoc = $this->generateSubjects();
        $daysOfWeek = $this->generateDaysOfWeek();
        $semester = $this->generateSemester();

        // dd($listHocSinh);
        return response()->json($this->assignClasses($listHocSinh, $listPhonghoc, $listMonHoc, $daysOfWeek, $semester));
    }

    private function generateStudentList()
    {
        $studentList = DB::table('users')->where('role', '=', '3')->where('is_active', '=', 1)->get();
        $students = $studentList->map(function ($student) {
            return [
                'maHS' => $student->user_code,
                'ten' => $student->full_name,
                'chuyenNganh' => $student->major_code,
                'chuyenNganhHep' => $student->narrow_major_code,
                'hocKy' => $student->semester_code,
                'khoaHoc' => $student->course_code,
            ];
        })->toArray();
        // return dd($students);
        return $students;
    }

    private function generateTeacherList()
    {
        $teacherList = DB::table('users')->where('role', '=', '2')->where('is_active', '=', true)->get();
        $teachers = $teacherList->map(function ($teacher) {
            return [
                'maGV' => $teacher->user_code,
                'ten' => $teacher->full_name,
                'chuyenNganh' => $teacher->major_code,
                'chuyenNganhHep' => $teacher->narrow_major_code,
            ];
        })->toArray();
        return $teachers;
    }

    private function generateClassrooms()
    {
        $rooms = DB::table('categories')->where('type', '=', "school_room")->where('is_active', '=', true)->get();
        $classRooms = $rooms->map(function ($room) {
            return [
                'code' => $room->cate_code,
                'name' => $room->cate_name,
                'sucChua' => $room->value
            ];
        })->toArray();
        // dd($classRooms);
        return $classRooms;
    }

    private function generateSemester()
    {
        $rooms = DB::table('categories')->where('type', '=', "semester")->where('is_active', '=', true)->get();
        $classRooms = $rooms->map(function ($room) {
            return [
                'code' => $room->cate_code,
                'name' => $room->cate_name,
            ];
        })->toArray();
        return $classRooms;
    }

    private function generateSubjects()
    {
        $subjects = DB::table('subjects')->where('is_active', '=', true)->get();
        $subjectList = $subjects->map(function ($subject) {
            return [
                'code' => $subject->subject_code,
                'name' => $subject->subject_name,
                'tinChi' => $subject->credit_number,
                'chuyenNganh' => $subject->major_code,
                'hocKy' => $subject->semester_code,
            ];
        })->toArray();
        // return dd($subjectList);
        return $subjectList;
    }

    private function generateDaysOfWeek()
    {
        return [
            ["code" => "thu2", "name" => "Thứ Hai"],
            ["code" => "thu3", "name" => "Thứ Ba"],
        ];
    }

    // private function assignClasses($listHocSinh, $listPhonghoc, $listMonHoc, $daysOfWeek, $semesters)
    // {
    //     // dd($semesters);
    //     // dd($listHocSinh);

    //     $listLop = []; // Danh sách lớp học đã xếp
    //     $classTimes = $this->generateClassTimes(); // Danh sách ca học
    //     $teachersInMajor = $this->generateTeacherList();
    //     // dd($teachersInMajor);
    //     // Lưu trạng thái hiện tại của ngày, ca, và phòng cho mỗi môn
    //     $currentDayIndex = 0;
    //     $currentRoomIndex = 0;
    //     $currentTimeIndex = 0;

    //     // Lưu số buổi dạy của từng giảng viên
    //     $teacherWorkload = [];

    //     // Giữ phòng hiện tại của từng giảng viên
    //     $teacherRoom = [];
    //     // Duyệt qua từng môn học
    //     foreach ($semesters as $hocKy) {
    //         $hocKyHienTai = $hocKy['code'];

    //         foreach ($listMonHoc as $mon) {
    //             // Chỉ lọc các môn học thuộc học kỳ hiện tại
    //             if ($mon['hocKy'] !== $hocKyHienTai) {
    //                 continue; // Bỏ qua nếu môn không thuộc học kỳ hiện tại
    //             }

    //             // Bộ đếm lớp học cho mỗi môn
    //             $classCounter = 1;

    //             // Lọc danh sách học sinh theo chuyên ngành và học kỳ của môn học
    //             $studentsInClass = array_filter($listHocSinh, function ($hs) use ($mon, $hocKyHienTai) {
    //                 return ($hs['chuyenNganh'] === $mon['chuyenNganh'] || $hs['chuyenNganhHep'] === $mon['chuyenNganh'])
    //                     && $hs['hocKy'] === $hocKyHienTai;
    //             });


    //             // dd($studentsInClass);
    //             // dd($mon['chuyenNganh'],$hocKyHienTai);
    //             // Lấy danh sách giảng viên theo chuyên ngành của môn học

    //             $teachersForClass = array_filter($teachersInMajor, function ($gv) use ($mon) {
    //                 return $gv['chuyenNganh'] === $mon['chuyenNganh'] || $gv['chuyenNganhHep'] === $mon['chuyenNganh']; // Lọc giảng viên theo chuyên ngành
    //             });

    //             // Sắp xếp giảng viên theo số buổi dạy hiện tại, ưu tiên những người có ít buổi dạy hơn
    //             usort($teachersForClass, function ($a, $b) use ($teacherWorkload) {
    //                 return ($teacherWorkload[$a['maGV']] ?? 0) <=> ($teacherWorkload[$b['maGV']] ?? 0);
    //             });

    //             // Chọn giảng viên
    //             $currentTeacher = reset($teachersForClass);

    //             if (!$currentTeacher) {
    //                 // Nếu không có giảng viên phù hợp, để trống thông tin giảng viên
    //                 $currentTeacher = ['ten' => null];
    //                 $currentTeacher = ['maGV' => null];
    //             } else {

    //                 // Nếu giảng viên đã có phòng, ưu tiên giữ nguyên phòng
    //                 if (isset($teacherRoom[$currentTeacher['maGV']]) == true) {
    //                     $currentRoomIndex = array_search($teacherRoom[$currentTeacher['maGV']], array_column($listPhonghoc, 'code'));
    //                 }
    //             }

    //             // Tiếp tục vòng lặp qua các ngày, ca, và phòng học, bắt đầu từ trạng thái hiện tại
    //             $currentStudentIndex = 0; // Chỉ số học sinh bắt đầu từ 0 cho mỗi môn học
    //             $totalStudents = count($studentsInClass); // Tổng số học sinh cho môn này

    //             // Tiếp tục vòng lặp qua các ngày, ca, và phòng học, bắt đầu từ trạng thái hiện tại
    //             while ($currentStudentIndex < $totalStudents) {
    //                 // Kiểm tra nếu đã hết các ngày trong tuần
    //                 if ($currentDayIndex >= count($daysOfWeek)) {
    //                     break; // Dừng lại khi đã hết các ngày
    //                 }

    //                 // Lấy ngày hiện tại
    //                 $day = $daysOfWeek[$currentDayIndex];

    //                 // Lấy phòng học hiện tại
    //                 $phong = $listPhonghoc[$currentRoomIndex];

    //                 // Lấy ca học hiện tại
    //                 $classTime = $classTimes[$currentTimeIndex];
    //                 $listUser = $this->getStudentsInSameClassOrSession($classTime['code']);
    //                 // dd($listUser);
    //                 // Sức chứa của phòng học
    //                 $roomCapacity = $phong['sucChua'];

    //                 // Số lượng học sinh tối đa trong lớp là sức chứa của phòng hoặc số học sinh còn lại
    //                 $classSize = min($roomCapacity, $totalStudents - $currentStudentIndex);
    //                 if ($this->isConflict($listLop, $day['code'], $classTime['code'], $phong['code'])) {
    //                     $currentTimeIndex++;
    //                     if ($currentTimeIndex >= count($classTimes)) {
    //                         $currentTimeIndex = 0;
    //                         $currentRoomIndex++;
    //                         if ($currentRoomIndex >= count($listPhonghoc)) {
    //                             $currentRoomIndex = 0;
    //                             $currentDayIndex++;
    //                         }
    //                     }
    //                     continue;
    //                 }
    //                 // $dataStudents = $this->getListUserByClassRooms($phong['code'], $classTime['code'], $mon['code']);
    //                 // $dataStudents = $this->getListUserByClassRooms($classTime['code']);
    //                 // $validTimeIndex = $this->findValidClassTime($studentsInClass, $mon['code'], $day['code'], $currentTimeIndex, $classTimes);
    //                 // dd($classSize);
    //                 // $studentsToAdd = [];
    //                 // foreach (array_slice($studentsInClass, $currentStudentIndex, $classSize) as $student) {
    //                 //     $status = $this->checkStudentScheduleConflict(
    //                 //         $student['maHS'],
    //                 //         $mon['code'],
    //                 //         $phong['code'],
    //                 //         $classTime['code'],
    //                 //         $day['code']
    //                 //     );

    //                 //     if ($status === 'Không trùng') {
    //                 //         $studentsToAdd[] = $student;
    //                 //     } elseif ($status === 'Trùng ca học') {
    //                 //         $currentTimeIndex++;
    //                 //         if ($currentTimeIndex >= count($classTimes)) {
    //                 //             $currentTimeIndex = 0;
    //                 //             $currentRoomIndex++;
    //                 //             if ($currentRoomIndex >= count($listPhonghoc)) {
    //                 //                 $currentRoomIndex = 0;
    //                 //                 $currentDayIndex++;
    //                 //             }
    //                 //         }
    //                 //     }
    //                 // }

    //                 // if (empty($studentsToAdd)) break;

    //                 // dd($phong['code'], $classTime['code'], $mon['code']);
    //                 // dd($dataStudents,$studentsInClass);
    //                 // Kiểm tra nếu số học sinh còn lại để xếp lớp nhỏ hơn hoặc bằng 0
    //                 // dd($classSize);
    //                 if ($classSize <= 0) {

    //                     break; // Thoát vòng lặp nếu không còn học sinh để xếp lớp
    //                 }

    //                 // Tạo tên lớp, ví dụ: "Lớp MH101 - 1"
    //                 $className = "Lớp " . $mon['code'] . " - " . $classCounter;

    //                 // Tạo lớp cho môn học trong phòng này, ngày này, ca này
    //                 $listLop[] = [
    //                     "tenLop" => $className, // Tên lớp
    //                     "monHoc" => $mon['code'],
    //                     "phongHoc" => $phong['code'],
    //                     "ngay" => $day['code'], // Ngày học
    //                     "ca" => $classTime['code'], // Ca học
    //                     "giangVien" => $currentTeacher['ten'] ?? Null, // Giảng viên dạy
    //                     // "hocSinh" => array_slice($studentsInClass, $currentStudentIndex, $classSize), // Lấy tiếp học sinh từ vị trí hiện tại
    //                 ];

    //                 // Cập nhật chỉ số học sinh đã xếp vào lớp
    //                 $currentStudentIndex += $classSize; // Tăng chỉ số học sinh để không bị lặp
    //                 // dd($currentStudentIndex);
    //                 // Tăng số buổi dạy của giảng viên
    //                 if (!isset($teacherWorkload[$currentTeacher['maGV']])) {
    //                     $teacherWorkload[$currentTeacher['maGV']] = 0;
    //                 }
    //                 $teacherWorkload[$currentTeacher['maGV']]++;

    //                 // Lưu lại phòng hiện tại của giảng viên
    //                 $teacherRoom[$currentTeacher['maGV']] = $phong['code'];

    //                 // Tăng bộ đếm lớp cho môn học
    //                 $classCounter++;

    //                 // Cập nhật chỉ số ca học
    //                 $currentTimeIndex++;

    //                 // Nếu đã hết các ca trong ngày, chuyển sang phòng học tiếp theo
    //                 if ($currentTimeIndex >= count($classTimes)) {
    //                     $currentTimeIndex = 0;
    //                     // Kiểm tra phòng kế tiếp
    //                     $nextRoom = $this->findNextRoom($listPhonghoc, $phong, $currentRoomIndex);
    //                     $currentRoomIndex = array_search($nextRoom['code'], array_column($listPhonghoc, 'code'));
    //                 }

    //                 // Nếu đã hết các phòng trong ngày, chuyển sang ngày tiếp theo
    //                 if ($currentRoomIndex >= count($listPhonghoc)) {
    //                     $currentRoomIndex = 0;
    //                     $currentDayIndex++;
    //                 }

    //                 // Kiểm tra nếu đã hết các ngày trong tuần và thoát khỏi vòng lặp
    //                 if ($currentDayIndex >= count($daysOfWeek)) {
    //                     break; // Dừng lại khi đã hết các ngày trong tuần
    //                 }
    //             }
    //         }
    //     }
    //     return $listLop;
    // }

    // private function isConflict($listLop, $day, $classTime, $phong) {
    //     foreach ($listLop as $lop) {
    //         if ($lop['ngay'] === $day && $lop['ca'] === $classTime && $lop['phongHoc'] === $phong) {
    //             return true;
    //         }
    //     }
    //     return false;
    // }


    private function assignClasses($listHocSinh, $listPhonghoc, $listMonHoc, $daysOfWeek, $semesters)
    {
        $listLop = [];
        $classTimes = $this->generateClassTimes();
        $teachersInMajor = $this->generateTeacherList();
        $teacherWorkload = [];
        $teacherRoom = [];

        // Khởi tạo lịch trống cho từng phòng học và ca học theo từng ngày
        $roomSchedule = [];
        foreach ($listPhonghoc as $room) {
            foreach ($daysOfWeek as $day) {
                foreach ($classTimes as $classTime) {
                    $roomSchedule[$room['code']][$day['code']][$classTime['code']] = true; // true là phòng trống
                }
            }
        }

        foreach ($semesters as $hocKy) {
            $hocKyHienTai = $hocKy['code'];

            foreach ($listMonHoc as $mon) {
                if ($mon['hocKy'] !== $hocKyHienTai) {
                    continue;
                }

                $studentsInClass = array_filter($listHocSinh, function ($hs) use ($mon, $hocKyHienTai) {
                    return ($hs['chuyenNganh'] === $mon['chuyenNganh'] || $hs['chuyenNganhHep'] === $mon['chuyenNganh'])
                        && $hs['hocKy'] === $hocKyHienTai;
                });

                $teachersForClass = array_filter($teachersInMajor, function ($gv) use ($mon) {
                    return $gv['chuyenNganh'] === $mon['chuyenNganh'] || $gv['chuyenNganhHep'] === $mon['chuyenNganh'];
                });

                usort($teachersForClass, function ($a, $b) use ($teacherWorkload) {
                    return ($teacherWorkload[$a['maGV']] ?? 0) <=> ($teacherWorkload[$b['maGV']] ?? 0);
                });

                $currentTeacher = reset($teachersForClass);
                $currentTeacher = $currentTeacher ?: ['maGV' => null, 'ten' => null];
                $classCounter = 1;
                $currentStudentIndex = 0;
                $totalStudents = count($studentsInClass);

                while ($currentStudentIndex < $totalStudents) {
                    $classAssigned = false;

                    foreach ($daysOfWeek as $day) {
                        foreach ($classTimes as $classTime) {
                            foreach ($listPhonghoc as $phong) {
                                if ($roomSchedule[$phong['code']][$day['code']][$classTime['code']] === false) {
                                    continue;
                                }

                                $roomCapacity = $phong['sucChua'];
                                $classSize = min($roomCapacity, $totalStudents - $currentStudentIndex);
                                // dd($studentsInClass[0]['khoaHoc']);
                                $listLop[] = [
                                    "tenLop" => "Lớp " . $mon['code'] . "." . $classCounter,
                                    "maLop" => (isset($studentsInClass[0]['khoaHoc']) ? $studentsInClass[0]['khoaHoc'] : '') . "_" . $mon['code'] . "." . $classCounter,
                                    "monHoc" => $mon['code'],
                                    "phongHoc" => $phong['code'],
                                    "ngay" => $day['code'],
                                    "ca" => $classTime['code'],
                                    "giangVien" => $currentTeacher['ten'],
                                ];

                                $currentStudentIndex += $classSize;
                                $classCounter++;
                                $teacherWorkload[$currentTeacher['maGV']] = ($teacherWorkload[$currentTeacher['maGV']] ?? 0) + 1;
                                $teacherRoom[$currentTeacher['maGV']] = $phong['code'];

                                $roomSchedule[$phong['code']][$day['code']][$classTime['code']] = false;
                                $classAssigned = true;
                                break 3;
                            }
                        }
                    }

                    if (!$classAssigned) {
                        break;
                    }
                }
            }
        }
        $lopHocBD = $this->getListClassrooms();
        foreach ($listLop as $data) {
            $lop = array_filter($lopHocBD, function ($classroom) use ($data) {
                return $classroom->class_code == $data['maLop'];
            });
            // $lop = reset($lop);
            if (empty($lop)) {
                // dd($lop);
                Classroom::create([
                    'class_code' => $data['maLop'],
                    'class_name' => $data['tenLop'],
                    'description' => "Lớp học cho môn {$data['monHoc']}",
                    'is_active' => true,
                    'subject_code' => $data['monHoc'],
                    // 'user_code' => $data['giangVien']                      
                ]);

                // Schedule::create([
                //     'class_code' => $data['maLop'],
                //     'room_code' => $data['phongHoc'],
                //     'session_code' => $data['ca'],
                //     'date' => '2024-11-05',
                // ]);

                DB::table('schedules')->insert([
                    'class_code' => $data['maLop'],
                    'room_code' => $data['phongHoc'],
                    'session_code' => $data['ca'],
                    'date' => now()
                ]);
            }
            // continue;
        }

        return $listLop;
    }

    public function getListClassrooms()
    {
        $data = DB::table('classrooms')->where('is_active', true)->get()->toArray();
        return $data;
    }

    public function getClassroomsWithDetails()
    {
        $data = DB::table('classrooms')
            ->leftJoin('classroom_user', 'classrooms.class_code', '=', 'classroom_user.class_code')
            ->leftJoin('schedules', 'classrooms.class_code', '=', 'schedules.class_code')
            ->leftJoin('subjects', 'classrooms.subject_code', '=', 'subjects.subject_code') // Join thêm bảng subjects
            ->select(
                'subjects.subject_code',
                'subjects.subject_name', // Thêm tên môn học
                'classrooms.class_code',
                'classrooms.class_name',
                'classrooms.user_code'
            )
            ->where('classrooms.is_active', true)
            ->get()
            ->groupBy('subject_code') // Nhóm theo mã môn học
            ->map(function ($classes) {
                return $classes->toArray(); // Chuyển từng nhóm lớp học thành mảng
            })
            ->toArray();

        return $data;
    }


    public function getCategoriesWithClassrooms()
    {
        $data = DB::table('categories')->where('categories.is_active', true)
            ->where('categories.type', 'major')
            ->leftJoin('subjects', 'categories.cate_code', '=', 'subjects.major_code')->where('subjects.is_active', true)
            ->leftJoin('classrooms', 'classrooms.subject_code', '=', 'subjects.subject_code')->where('classrooms.is_active', true)
            ->leftJoin('classroom_user', 'classrooms.class_code', '=', 'classroom_user.class_code')
            ->leftJoin('schedules', 'classrooms.class_code', '=', 'schedules.class_code')
            ->select(
                'categories.cate_code',
                'categories.cate_name',
                'categories.parent_code',
                'classrooms.class_code',
                'classrooms.class_name',
                'classrooms.subject_code',
                'classrooms.user_code'
            )
            ->get()
            ->groupBy('cate_code') // Nhóm theo mã danh mục
            ->map(function ($classrooms) {
                $category = $classrooms->first(); // Lấy thông tin chung của category
                $category->classrooms = $classrooms->map(function ($classroom) {
                    return [
                        'class_code' => $classroom->class_code,
                        'class_name' => $classroom->class_name,
                        'subject_code' => $classroom->subject_code,
                        'user_code' => $classroom->user_code,
                    ];
                })->toArray();
                return $category;
            })
            ->values()
            ->toArray();

        return $data;
    }

    public function getListStudentByMajor()
    {
        $data = DB::table('categories')
            ->where('categories.is_active', true)
            ->where('categories.type', 'major')
            ->leftJoin('subjects', 'categories.cate_code', '=', 'subjects.major_code')
            ->where('subjects.is_active', true)
            ->leftJoin('users as major_users', function ($join) {
                $join->on('categories.cate_code', '=', 'major_users.major_code')
                    ->orOn('categories.cate_code', '=', 'major_users.narrow_major_code');
            })
            ->where('major_users.is_active', true)
            ->where('major_users.role', '3')
            ->select(
                'categories.cate_code',
                'categories.cate_name',
                'subjects.subject_code',
                'subjects.subject_name',
                'subjects.semester_code as subject_semester_code',
                'major_users.user_code as major_user_code',
                'major_users.full_name as major_user_name',
                'major_users.semester_code as major_semester_code',
            )
            ->get()
            ->groupBy('cate_code') // Nhóm theo mã chuyên ngành
            ->map(function ($subjects, $cate_code) {
                return [
                    'cate_code' => $cate_code,
                    'cate_name' => $subjects->first()->cate_name,
                    'subjects' => $subjects->groupBy('subject_code')->map(function ($students, $subject_code) {
                        // Lấy danh sách học sinh duy nhất
                        $uniqueStudents = collect();
                        foreach ($students as $student) {
                            // Chỉ lấy học sinh có kỳ học trùng với kỳ học của môn học
                            if ($student->major_semester_code === $student->subject_semester_code) {
                                $uniqueStudents->push([
                                    'user_code' => $student->major_user_code,
                                    'user_name' => $student->major_user_name,
                                    'semester' => $student->major_semester_code,
                                ]);
                            }
                        }
                        // Sử dụng unique() để loại bỏ các bản sao
                        return [
                            'subject_code' => $subject_code,
                            'subject_name' => $students->first()->subject_name,
                            'students' => $uniqueStudents->unique('user_code')->values()->toArray() // Chỉ giữ lại bản ghi duy nhất theo user_code
                        ];
                    })->values()->toArray() // Sắp xếp lại mảng chỉ số
                ];
            })->values()->toArray();

        return $data;
    }

    public function getListByMajor()
    {
        $studentRelearns = DB::table('scores')
            ->join('users', 'scores.student_code', '=', 'users.user_code')
            ->where('scores.is_pass', 0)
            ->where('scores.status', 1)
            ->select(
                'scores.subject_code',
                'users.user_code',
                'users.full_name as user_name',
                'users.semester_code'
            )
            ->get();

        $studentRelearnsGrouped = $studentRelearns->groupBy('subject_code');
        $data = DB::table('categories')->where([
            'categories.is_active' =>  true,
            'categories.type' => 'major'
        ])
            ->leftJoin('subjects', 'categories.cate_code', '=', 'subjects.major_code')
            ->where('subjects.is_active', true)
            ->leftJoin('users as major_users', function ($join) {
                $join->on('categories.cate_code', '=', 'major_users.major_code')
                    ->orOn('categories.cate_code', '=', 'major_users.narrow_major_code')
                    ->orWhere('categories.cate_code', 'LIKE', 'ALL%');
            })
            ->leftJoin('fees', 'major_users.user_code', '=', 'fees.user_code')
            ->where('major_users.is_active', true)
            ->whereIn('major_users.role', ["2", "3"])
            ->select(
                'categories.cate_code',
                'categories.cate_name',
                'subjects.subject_code',
                'subjects.subject_name',
                'subjects.major_code as subject_major_code',
                'subjects.semester_code as subject_semester_code',
                'major_users.user_code as major_user_code',
                'major_users.major_code as major_code',
                'major_users.narrow_major_code as narrow_major_code',
                'major_users.full_name as major_user_name',
                'major_users.semester_code as major_semester_code',
                'major_users.role as user_role',
                'fees.status as fee_status',
                'fees.semester_code as semester_code',
            )
            ->get()
            ->groupBy('cate_code')
            ->map(function ($subjects, $cate_code) use ($studentRelearnsGrouped) {
                return [
                    'cate_code' => $cate_code,
                    'cate_name' => $subjects->first()->cate_name,
                    'subjects' => $subjects->groupBy('subject_code')->map(function ($users, $subject_code) use ($studentRelearnsGrouped) {
                        $students = collect();
                        $teachers = collect();
                        $subjectIsForAll = substr($users->first()->subject_code, 0, 3) === 'ALL';
                        // return $users;
                        foreach ($users as $user) {
                            if ($user->user_role == "3" && $user->fee_status === "paid" && $user->semester_code === $user->subject_semester_code && $user->major_semester_code === $user->subject_semester_code) {
                                $students->push([
                                    'user_code' => $user->major_user_code,
                                    'user_name' => $user->major_user_name,
                                    'semester' => $user->major_semester_code,
                                ]);
                            }
                            if ($subjectIsForAll) {
                                if ($user->user_role == "2" && ($user->major_code == $user->subject_major_code || $user->narrow_major_code == $user->subject_major_code)) {
                                    $teachers->push([
                                        'user_code' => $user->major_user_code,
                                        'user_name' => $user->major_user_name,
                                    ]);
                                }
                            } else {
                                if ($user->user_role == "2") {
                                    $teachers->push([
                                        'user_code' => $user->major_user_code,
                                        'user_name' => $user->major_user_name,
                                    ]);
                                }
                            }
                        }

                        // Bổ sung sinh viên học lại
                        // return $studentRelearnsGrouped[$subject_code];
                        if (isset($studentRelearnsGrouped[$subject_code])) {
                            foreach ($studentRelearnsGrouped[$subject_code] as $student) {
                                $students->push([
                                    'user_code' => $student->user_code,
                                    'user_name' => $student->user_name,
                                    'semester' => $student->semester_code,
                                ]);
                            }
                        }

                        return [
                            'subject_code' => $subject_code,
                            'subject_name' => $users->first()->subject_name,
                            'students' => $students->unique('user_code')->values()->toArray(),
                            'teachers' => $teachers->unique('user_code')->values()->toArray(),
                        ];
                    })->values()->toArray()
                ];
            })->values()->toArray();

        return $data;
    }


    public function getClassrooms()
    {
        $data = DB::table('classrooms')
            ->where('classrooms.is_active', true)
            ->whereDate('classrooms.created_at', '=', date('Y-m-d'))
            ->leftJoin('classroom_user', 'classrooms.class_code', '=', 'classroom_user.class_code')
            ->leftJoin('schedules', 'classrooms.class_code', '=', 'schedules.class_code')
            ->leftJoin('categories', 'schedules.room_code', '=', 'categories.cate_code')
            ->where('categories.is_active', true)
            ->where('categories.type', 'school_room')
            ->orderBy('categories.cate_name', 'asc')  // Sắp xếp theo phòng học
            ->orderBy('schedules.session_code', 'asc') // Sắp xếp theo ca học
            // ->orderBy('schedules.date', 'asc') // Sắp xếp theo ngày học
            ->select(
                'categories.cate_code',
                'categories.cate_name',
                'categories.value',
                'classrooms.class_code',
                'classrooms.class_name',
                'classrooms.subject_code',
                'schedules.session_code',
                'schedules.date',
                'classrooms.user_code'
            )
            ->get()
            ->toArray();

        return $data;
    }


    // public function generateSchedule()
    // {
    //     $data = DB::table('schedules')
    //         ->leftJoin('classrooms', 'classrooms.class_code', '=', 'schedules.class_code')
    //         ->whereDate('classrooms.created_at', '=', date('Y-m-d'))
    //         ->leftJoin('subjects', 'classrooms.subject_code', '=', 'subjects.subject_code')
    //         ->select(
    //             'schedules.*',
    //             'subjects.total_sessions'
    //         )
    //         ->get();

    //     $createdDates = []; // Mảng lưu các ngày cần tạo
    //     $insertData = []; // Mảng để lưu dữ liệu chờ insert vào DB

    //     foreach ($data as $item) {
    //         $startDate = Carbon::parse($item->date); // Ngày ban đầu
    //         $totalSessions = $item->total_sessions; // Số buổi cần tạo
    //         $currentSession = 0; // Biến đếm số buổi đã tạo

    //         // Xác định ngày trong tuần ban đầu
    //         $startDayOfWeek = $startDate->dayOfWeek;

    //         // Mảng chứa các ngày trong tuần sẽ tạo
    //         $weekDays = [];

    //         // Xác định ngày tiếp theo cần lấy (thứ 2-4-6, hoặc thứ 3-5-7,...)
    //         if ($startDayOfWeek == 1) { // Thứ 2
    //             $weekDays = [1, 3, 5]; // Thứ 2, thứ 4, thứ 6
    //         } elseif ($startDayOfWeek == 2) { // Thứ 3
    //             $weekDays = [2, 4, 6]; // Thứ 3, thứ 5, thứ 7
    //         } elseif ($startDayOfWeek == 3) { // Thứ 4
    //             $weekDays = [3, 5]; // Thứ 4, thứ 6
    //         } elseif ($startDayOfWeek == 4) { // Thứ 5
    //             $weekDays = [4, 6]; // Thứ 5, thứ 7
    //         } elseif ($startDayOfWeek == 5) { // Thứ 6
    //             $weekDays = [5, 7]; // Thứ 6, thứ 2 tuần sau
    //         } elseif ($startDayOfWeek == 6) { // Thứ 7
    //             $weekDays = [6, 1]; // Thứ 7, thứ 3 tuần sau
    //         } else { // Chủ nhật
    //             $weekDays = [7, 2]; // Chủ nhật, thứ 4 tuần sau
    //         }

    //         // Lặp tạo ngày cho đến khi đạt đủ total_sessions
    //         while ($currentSession < $totalSessions) {
    //             if (in_array($startDate->dayOfWeek, $weekDays)) {
    //                 // Kiểm tra trùng lặp trong cơ sở dữ liệu
    //                 $exists = DB::table('schedules')
    //                     ->where('date', $startDate->format('Y-m-d'))
    //                     ->where('class_code', $item->class_code)
    //                     ->where('room_code', $item->room_code ?? null)
    //                     ->where('session_code', $item->session_code)
    //                     ->exists();

    //                 if (!$exists) {
    //                     $createdDates[] = $startDate->format('Y-m-d');
    //                     $insertData[] = [
    //                         'date' => $startDate->format('Y-m-d'),
    //                         'room_code' => $item->room_code ?? null,
    //                         'class_code' => $item->class_code,
    //                         'session_code' => $item->session_code,
    //                         'teacher_code' => $item->teacher_code,
    //                         'type' => 'study'
    //                     ];

    //                     $currentSession++;
    //                 }
    //             }

    //             // Tiến đến ngày tiếp theo
    //             $startDate->addDay();
    //         }
    //         // Sau khi hoàn tất các buổi học, tạo 3 buổi thi cách 1 tuần
    //         $examDays = [];
    //         $startDate->addWeek(); // Cách 1 tuần sau buổi học cuối cùng
    //         $examCount = 0; // Biến đếm số buổi thi

    //         while ($examCount < 3) {
    //             if (in_array($startDate->dayOfWeek, $weekDays)) {
    //                 // Kiểm tra trùng lặp trong cơ sở dữ liệu
    //                 $exists = DB::table('schedules')
    //                     ->where('date', $startDate->format('Y-m-d'))
    //                     ->where('class_code', $item->class_code)
    //                     ->where('room_code', $item->room_code ?? null)
    //                     ->where('session_code', $item->session_code)
    //                     ->exists();

    //                 if (!$exists) {
    //                     $examDays[] = $startDate->format('Y-m-d');
    //                     $insertData[] = [
    //                         'date' => $startDate->format('Y-m-d'),
    //                         'room_code' => $item->room_code ?? null,
    //                         'class_code' => $item->class_code,
    //                         'session_code' => $item->session_code,
    //                         'teacher_code' => $item->teacher_code,
    //                         'type' => 'exam' // Loại buổi thi
    //                     ];

    //                     $examCount++;
    //                 }
    //             }

    //             // Tiến đến ngày tiếp theo
    //             $startDate->addDay();
    //         }

    //         $createdDates = array_merge($createdDates, $examDays); // Gộp các ngày đã tạo
    //     }

    //     // Chèn dữ liệu vào bảng schedules
    //     DB::table('schedules')->insert($insertData);

    //     return response()->json([
    //         'created_dates' => $createdDates,
    //         'message' => 'Schedules have been generated and inserted successfully!'
    //     ]);
    // }

    public function generateSchedule()
    {
        $data = DB::table('schedules')
            ->leftJoin('classrooms', 'classrooms.class_code', '=', 'schedules.class_code')
            ->whereDate('classrooms.created_at', '=', date('Y-m-d'))
            ->leftJoin('subjects', 'classrooms.subject_code', '=', 'subjects.subject_code')
            ->select(
                'schedules.*',
                'subjects.total_sessions'
            )
            ->get();

        if ($data->isEmpty()) {
            return response()->json([
                'message' => 'No schedules found for today!'
            ], 404);
        }

        $existingSchedules = DB::table('schedules')
            ->select('date', 'class_code', 'room_code', 'session_code')
            ->get()
            ->keyBy(function ($item) {
                return $item->date . '-' . $item->class_code . '-' . $item->room_code . '-' . $item->session_code;
            });

        $createdDates = [];
        $insertData = [];

        foreach ($data as $item) {
            $startDate = Carbon::parse($item->date);
            $totalSessions = $item->total_sessions;
            $currentSession = 0;

            // Xác định các ngày trong tuần (2-4-6 hoặc 3-5-7)
            $weekDays = in_array($startDate->dayOfWeek, [1, 3, 5]) ? [1, 3, 5] : [2, 4, 6];

            // Tiến đến ngày hợp lệ đầu tiên trong tuần
            while (!in_array($startDate->dayOfWeek, $weekDays)) {
                $startDate->addDay();
                return $weekDays;
            }

            // Tạo buổi học theo lịch 2-4-6 hoặc 3-5-7
            while ($currentSession < $totalSessions - 3) {
                $key = $startDate->format('Y-m-d') . '-' . $item->class_code . '-' . ($item->room_code ?? '') . '-' . $item->session_code;

                if (!isset($existingSchedules[$key])) {
                    $createdDates[] = $startDate->format('Y-m-d');
                    $insertData[] = [
                        'date' => $startDate->format('Y-m-d'),
                        'room_code' => $item->room_code ?? null,
                        'class_code' => $item->class_code,
                        'session_code' => $item->session_code,
                        'teacher_code' => $item->teacher_code,
                        'type' => 'study'
                    ];

                    $currentSession++;
                }

                // Tăng ngày học tiếp theo trong tuần (chỉ chọn 2-4-6 hoặc 3-5-7)
                do {
                    $startDate->addDay();
                } while (!in_array($startDate->dayOfWeek, $weekDays));
            }

            // Thêm lịch thi (3 buổi cách 1 tuần sau buổi cuối)
            $startDate->addWeek();
            $examCount = 0;

            while ($examCount < 3) {
                $key = $startDate->format('Y-m-d') . '-' . $item->class_code . '-' . ($item->room_code ?? '') . '-' . $item->session_code;

                if (!isset($existingSchedules[$key]) && in_array($startDate->dayOfWeek, $weekDays)) {
                    $insertData[] = [
                        'date' => $startDate->format('Y-m-d'),
                        'room_code' => $item->room_code ?? null,
                        'class_code' => $item->class_code,
                        'session_code' => $item->session_code,
                        'teacher_code' => $item->teacher_code,
                        'type' => 'exam'
                    ];

                    $examCount++;
                }

                // Tăng ngày thi tiếp theo (chỉ chọn 2-4-6 hoặc 3-5-7)
                do {
                    $startDate->addDay();
                } while (!in_array($startDate->dayOfWeek, $weekDays));
            }
        }

        DB::table('schedules')->upsert($insertData, ['date', 'class_code', 'room_code', 'session_code'], ['type', 'teacher_code']);

        return response()->json([
            'created_dates' => $createdDates,
            'message' => 'Schedules have been generated and inserted successfully!'
        ]);
    }





    public function generateAttendances()
    {
        $schedules = DB::table('schedules')->get();

        foreach ($schedules as $schedule) {
            // Lấy danh sách sinh viên trong lớp của lịch học
            $students = DB::table('classroom_user')
                ->where('class_code', $schedule->class_code)
                ->whereDate('created_at', now())
                ->get();

            foreach ($students as $student) {
                // Tạo bản ghi điểm danh
                DB::table('attendances')->insert([
                    'student_code' => $student->user_code,
                    'class_code' => $schedule->class_code,
                    'date' => $schedule->date,
                    'status' => 'present', // Trạng thái mặc định
                    'noted' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }



        return response()->json([
            'message' => 'Tạo điểm danh thành công!'
        ]);
    }


    // public function getListStudentByMajor()
    // {
    //     $data = DB::table('categories')
    //         ->where('categories.is_active', true)
    //         ->where('categories.type', 'major')
    //         ->leftJoin('subjects', 'categories.cate_code', '=', 'subjects.major_code')
    //         ->where('subjects.is_active', true)
    //         ->leftJoin('users as major_users', 'categories.cate_code', '=', 'major_users.major_code')
    //         ->where('major_users.is_active', true)
    //         ->where('major_users.role', '3')
    //         ->leftJoin('classroom_user', function ($join) {
    //             $join->on('classroom_user.user_code', '=', 'major_users.user_code');
    //         })
    //         ->leftJoin('classrooms', 'subjects.subject_code', '=', 'classrooms.subject_code')
    //         ->leftJoin('schedules', 'schedules.class_code', '=', 'classrooms.class_code')
    //         ->leftJoin('categories as schoolrooms', 'schedules.room_code', '=', 'schoolrooms.cate_code')
    //         ->whereNull('classroom_user.user_code') // Filter out students already assigned to a class
    //         ->whereRaw('(
    //             SELECT COUNT(*) FROM classroom_user 
    //             WHERE classroom_user.class_code = classrooms.class_code
    //         ) <= schoolrooms.value') // Check classroom capacity
    //         ->select(
    //             'categories.cate_code',
    //             'categories.cate_name',
    //             'subjects.subject_code',
    //             'subjects.subject_name',
    //             'subjects.semester_code as subject_semester_code',
    //             'major_users.user_code as major_user_code',
    //             'major_users.full_name as major_user_name',
    //             'major_users.semester_code as major_semester_code',
    //             'classrooms.class_code',
    //             'classrooms.class_name',
    //             'schoolrooms.value as room_value'
    //         )
    //         ->get()
    //         ->groupBy('cate_code')
    //         ->map(function ($subjects, $cate_code) {
    //             return [
    //                 'cate_code' => $cate_code,
    //                 'cate_name' => $subjects->first()->cate_name,
    //                 'subjects' => $subjects->groupBy('subject_code')->map(function ($students, $subject_code) {
    //                     $classrooms = [];
    //                     $classroomInfo = [
    //                         'class_code' => $students->first()->class_code,
    //                         'class_name' => $students->first()->class_name,
    //                         'room_value' => $students->first()->room_value,
    //                     ];

    //                     $currentClassroom = $classroomInfo;
    //                     $currentStudents = collect();
    //                     foreach ($students as $student) {
    //                         if (($student->major_semester_code ?? $student->narrow_semester_code) === $student->subject_semester_code) {
    //                             // Kiểm tra và lưu phòng nếu đã đủ số lượng sinh viên
    //                             if ($currentStudents->count() >= $currentClassroom['room_value']) {
    //                                 // Lưu thông tin vào `classroom_user`
    //                                 if ($currentStudents->isNotEmpty()) {
    //                                     // Thay thế vòng lặp insert như sau:
    //                                     foreach ($currentStudents as $user) {
    //                                         ClassroomUser::firstOrCreate([
    //                                             'class_code' => $currentClassroom['class_code'],
    //                                             'user_code' => $user['user_code'],
    //                                         ]);
    //                                     }


    //                                     $classrooms[] = [
    //                                         'classroom' => $currentClassroom,
    //                                         'students' => $currentStudents->unique('user_code')->values()->toArray(),
    //                                     ];
    //                                 }
    //                                 $currentStudents = collect();
    //                             }

    //                             $currentStudents->push([
    //                                 'user_code' => $student->major_user_code,
    //                                 'user_name' => $student->major_user_name,
    //                                 'semester' => $student->major_semester_code ?? $student->narrow_semester_code,
    //                             ]);
    //                         }
    //                     }

    //                     return [
    //                         'subject_code' => $subject_code,
    //                         'subject_name' => $students->first()->subject_name,
    //                         'classrooms' => $classrooms,
    //                     ];
    //                 })->values()->toArray()
    //             ];
    //         })->values()->toArray();
    //     return $data;
    // }

    public function addStudent()
    {
        $classRooms = $this->getClassrooms(); // Lấy danh sách lớp học
        $majors = $this->getListByMajor();
        $classroomStudentCounts = DB::table('classroom_user')
            ->select('class_code', DB::raw('COUNT(*) as current_count'))
            ->groupBy('class_code')
            ->pluck('current_count', 'class_code')
            ->toArray();

        // Gán số lượng hiện tại là 0 nếu lớp chưa có dữ liệu
        foreach ($classRooms as $classRoom) {
            if (!isset($classroomStudentCounts[$classRoom->class_code])) {
                $classroomStudentCounts[$classRoom->class_code] = 0;
            }
        }

        $classRoomIndex = 0;
        $batchInsertData = []; // Lưu dữ liệu batch insert

        foreach ($majors as $major) {
            foreach ($major['subjects'] as $subject) {
                foreach ($subject['students'] as $student) {
                    $assigned = false;

                    // Kiểm tra tất cả các lớp học cho môn này
                    while ($classRoomIndex < count($classRooms)) {
                        $classRoom = $classRooms[$classRoomIndex];
                        $currentStudentCount = $classroomStudentCounts[$classRoom->class_code];
                        $classCapacity = intval($classRoom->value);

                        // Nếu lớp chưa đầy, kiểm tra sinh viên có thể học lớp này không
                        if ($currentStudentCount < $classCapacity) {
                            if ($this->canAssignStudentToClass($student, $classRoom)) {
                                // Thêm dữ liệu vào batch insert
                                $batchInsertData[] = [
                                    'class_code' => $classRoom->class_code,
                                    'user_code' => $student['user_code'],
                                ];

                                // Cập nhật số lượng sinh viên hiện tại
                                $classroomStudentCounts[$classRoom->class_code]++;
                                $this->updateClassroomForSubject($classRoom, $subject);
                                $assigned = true;
                                break;
                            }
                        } else {
                            // Nếu lớp đã đầy, chuyển sang lớp tiếp theo
                            $classRoomIndex++;
                        }
                    }

                    if (!$assigned) {
                        // Nếu không gán được sinh viên vào lớp hiện tại, tiếp tục vòng lặp
                        continue;
                    }
                }
                $classRoomIndex++;
            }
        }

        // Thực hiện batch insert để chèn tất cả sinh viên cùng lúc
        if (!empty($batchInsertData)) {
            DB::table('classroom_user')->insert($batchInsertData);
        }

        return response()->json(['message' => 'Students assigned to classrooms successfully']);
    }


    // public function addStudent()
    // {
    //     $classRooms = $this->getClassrooms(); // Lấy danh sách lớp học
    //     $majors = $this->getListByMajor();   // Lấy danh sách sinh viên theo chuyên ngành

    //     // Tạo một bộ nhớ tạm cho số lượng sinh viên đã gán vào mỗi lớp
    //     $classroomStudentCounts = DB::table('classroom_user')
    //         ->select('class_code', DB::raw('COUNT(*) as current_count'))
    //         ->groupBy('class_code')
    //         ->pluck('current_count', 'class_code')
    //         ->toArray();

    //     // Gán số lượng hiện tại là 0 nếu lớp chưa có dữ liệu
    //     foreach ($classRooms as $classRoom) {
    //         if (!isset($classroomStudentCounts[$classRoom->class_code])) {
    //             $classroomStudentCounts[$classRoom->class_code] = 0;
    //         }
    //     }

    //     $classRoomIndex = 0;
    //     $batchInsertData = []; // Lưu dữ liệu batch insert

    //     foreach ($majors as $major) {
    //         foreach ($major['subjects'] as $subject) {
    //             foreach ($subject['students'] as $student) {
    //                 $assigned = false;

    //                 while ($classRoomIndex < count($classRooms)) {
    //                     $classRoom = $classRooms[$classRoomIndex];
    //                     $currentStudentCount = $classroomStudentCounts[$classRoom->class_code];
    //                     $classCapacity = intval($classRoom->value);

    //                     if ($currentStudentCount < $classCapacity) {
    //                         if ($this->canAssignStudentToClass($student, $classRoom)) {
    //                             // Thêm dữ liệu vào batch insert
    //                             $batchInsertData[] = [
    //                                 'class_code' => $classRoom->class_code,
    //                                 'user_code' => $student['user_code'],
    //                             ];

    //                             // Cập nhật số lượng sinh viên hiện tại
    //                             $classroomStudentCounts[$classRoom->class_code]++;
    //                             $this->updateClassroomForSubject($classRoom, $subject);
    //                             $assigned = true;
    //                             break;
    //                         }
    //                     } else {
    //                         // Nếu lớp đã đầy, chuyển sang lớp tiếp theo
    //                         $classRoomIndex++;
    //                     }
    //                 }

    //                 if (!$assigned) {
    //                     continue;
    //                 }
    //             }
    //             $classRoomIndex++;
    //         }
    //     }

    //     // Thực hiện batch insert để chèn tất cả sinh viên cùng lúc
    //     if (!empty($batchInsertData)) {
    //         DB::table('classroom_user')->insert($batchInsertData);
    //     }

    //     return response()->json(['message' => 'Students assigned to classrooms successfully']);
    // }


    public function addTeacher()
    {
        try {
            DB::beginTransaction();

            // Lấy danh sách tất cả các lịch học chưa có giảng viên
            $schedules = DB::table('schedules')
                ->join('classrooms', 'schedules.class_code', '=', 'classrooms.class_code')
                ->join('subjects', 'classrooms.subject_code', '=', 'subjects.subject_code')
                ->select(
                    'schedules.id as schedule_id',
                    'schedules.date',
                    'schedules.session_code',
                    'schedules.class_code',
                    'subjects.major_code'
                )
                ->whereNull('schedules.teacher_code')
                ->orderBy('schedules.date')
                ->orderBy('schedules.session_code')
                ->get();

            // Lấy danh sách tất cả giảng viên đang hoạt động
            $teachers = DB::table('users')
                ->where('role', "2") // Giả sử role = 2 là giảng viên
                ->where('is_active', 1)
                ->select('user_code', 'major_code', 'narrow_major_code')
                ->get();

            // return $schedules;
            if ($teachers->isEmpty()) {
                return response()->json([
                    'message' => 'Không có giảng viên.',
                ], 400);
            }
            if ($schedules->isEmpty()) {
                return response()->json([
                    'message' => 'Không có lịch học cần xếp.',
                ], 400);
            }

            // Xếp giảng viên cho từng lịch
            foreach ($schedules as $schedule) {
                foreach ($teachers as $teacher) {
                    // Kiểm tra giảng viên có chuyên ngành phù hợp với môn học
                    if ($schedule->major_code !== $teacher->major_code && $schedule->major_code !== $teacher->narrow_major_code) {
                        continue; // Nếu không khớp chuyên ngành, bỏ qua giảng viên này
                    }

                    // Kiểm tra ràng buộc không trùng ca học cùng ngày
                    $conflict = DB::table('schedules')
                        ->where('teacher_code', $teacher->user_code)
                        ->where('date', $schedule->date)
                        ->where('session_code', $schedule->session_code)
                        ->exists();

                    if (!$conflict) {
                        // Gán giảng viên vào lịch học
                        DB::table('schedules')
                            ->where('id', $schedule->schedule_id)
                            ->update(['teacher_code' => $teacher->user_code]);

                        // Đồng thời cập nhật user_code trong bảng classrooms
                        DB::table('classrooms')
                            ->where('class_code', $schedule->class_code)
                            ->update(['user_code' => $teacher->user_code]);

                        // Chuyển sang lịch tiếp theo sau khi xếp thành công
                        break;
                    }
                }
            }

            DB::commit();
            $this->updateClassroomCodes();

            return response()->json([
                'message' => 'Xếp giảng viên vào lịch học thành công.',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Lỗi khi xếp lịch: ' . $e->getMessage(),
            ], 500);
        }
    }




    public function updateClassroomCodes()
    {
        // Lấy danh sách classrooms kèm thông tin course_code và major_code từ bảng users thông qua classroom_user
        $classrooms = DB::table('classrooms')
            ->join('classroom_user', 'classrooms.class_code', '=', 'classroom_user.class_code')
            ->join('users', 'classroom_user.user_code', '=', 'users.user_code')
            ->select(
                'classrooms.id',
                'classrooms.subject_code',
                'classrooms.class_code',
                DB::raw('GROUP_CONCAT(DISTINCT users.course_code) as course_code'),
                DB::raw('GROUP_CONCAT(DISTINCT users.major_code) as major_code')
            )
            ->groupBy('classrooms.id', 'classrooms.subject_code', 'classrooms.class_code')
            ->get();


        // Mảng lưu số thứ tự lớp học cho mỗi môn và khóa học
        $subjectCounters = [];

        foreach ($classrooms as $classroom) {
            $subjectCode = $classroom->subject_code;
            $courseCode = $classroom->course_code;
            $majorCode = $classroom->major_code;

            // Nếu chưa tồn tại số thứ tự cho subject_code, course_code, và major_code, khởi tạo
            if (!isset($subjectCounters[$subjectCode][$courseCode][$majorCode])) {
                $subjectCounters[$subjectCode][$courseCode][$majorCode] = 1;
            }

            // Tạo class_code và class_name mới
            $newClassCode = "{$courseCode}.{$subjectCode}{$subjectCounters[$subjectCode][$courseCode][$majorCode]}";
            // $newClassName = "Lớp_{$subjectCode}_Khóa_{$courseCode}_Ngành_{$majorCode}_{$subjectCounters[$subjectCode][$courseCode][$majorCode]}";
            $newClassName = "{$courseCode}.{$subjectCode}{$subjectCounters[$subjectCode][$courseCode][$majorCode]}";

            // Cập nhật `class_code` và `class_name` vào bảng classrooms
            DB::table('classrooms')
                ->where('id', $classroom->id)
                ->update([
                    'class_code' => $newClassCode,
                    'class_name' => $newClassName,
                ]);

            // Tăng số thứ tự
            $subjectCounters[$subjectCode][$courseCode][$majorCode]++;
        }
        DB::table('classrooms')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('classroom_user')
                    ->whereColumn('classroom_user.class_code', 'classrooms.class_code');
            })
            ->orWhereNull('classrooms.subject_code')
            ->delete();

        return response()->json(['message' => 'Classrooms updated successfully']);
    }

    public function updateClassroomForSubject($classRoom, $subject)
    {
        // Cập nhật lớp học cho môn học sau khi sinh viên được phân bổ vào lớp học
        DB::table('classrooms')
            ->where('class_code', $classRoom->class_code)
            ->update([
                'subject_code' => $subject['subject_code'],  // Cập nhật môn học cho lớp
                'class_name' => $classRoom->class_name,  // Cập nhật tên lớp học (nếu cần)
            ]);
    }

    public function canAssignStudentToClass($student, $classRoom)
    {
        // Lấy thông tin lịch học của lớp học từ bảng schedules
        $classSchedule = DB::table('schedules')
            ->where('room_code', $classRoom->class_code) // Lấy lịch học của lớp
            ->get();


        // Kiểm tra lịch học của sinh viên đã có từ bảng classroom_user
        // dd($student['user_code']);
        $studentSchedules = DB::table('classroom_user')
            ->join('schedules', 'classroom_user.class_code', '=', 'schedules.room_code')
            ->where('classroom_user.user_code', $student['user_code']) // Lấy lịch học của sinh viên
            ->get();

        // Duyệt qua từng lịch học của lớp học
        foreach ($classSchedule as $schedule) {
            // Kiểm tra nếu sinh viên đã có lịch học trùng với ca học và ngày học của lớp này
            foreach ($studentSchedules as $studentSchedule) {
                if ($studentSchedule->date == $schedule->date && $studentSchedule->session_code == $schedule->session_code) {
                    // Nếu sinh viên đã có lịch học trùng với lớp học này thì không thể gán
                    return false;
                }
            }
        }

        // Nếu không có lịch học trùng, có thể gán sinh viên vào lớp học
        return true;
    }



    public function getListClassByRoomAndSession(Request $request)
    {
        // try {
        $now = Carbon::now();  // Lấy thời gian hiện tại

        // Lấy ngày học cuối cùng của mỗi lớp
        DB::table('classrooms')
            ->join('schedules', 'classrooms.class_code', '=', 'schedules.class_code')  // Join bảng classrooms với schedules
            ->select('classrooms.id', DB::raw('MAX(schedules.date) as last_schedule_date'))
            ->groupBy('classrooms.id')  // Nhóm theo lớp học
            ->where('schedules.date', '<=', $now)  // Chỉ lấy lịch học trước hoặc bằng ngày hiện tại
            ->where('classrooms.is_active', '=', 1)  // Chỉ cập nhật lớp học đang hoạt động
            ->get()
            ->each(function ($classroom) {
                // Cập nhật trạng thái lớp học thành 'false' nếu ngày học cuối cùng đã qua
                DB::table('classrooms')
                    ->where('id', $classroom->id)
                    ->update(['is_active' => false]);
            });

        // $classrooms = DB::table('classrooms')
        //     ->join('schedules', 'classrooms.class_code', '=', 'schedules.class_code')  // Join bảng schedules với classrooms
        //     ->select('classrooms.id', 'classrooms.is_active', DB::raw('MAX(schedules.date) as last_schedule_date'))
        //     ->groupBy('classrooms.id')  // Nhóm theo lớp học
        //     ->get();

        // foreach ($classrooms as $classroom) {
        //     // Kiểm tra nếu ngày học cuối cùng <= ngày hiện tại
        //     if ($classroom->last_schedule_date <= $now) {
        //         // Cập nhật trạng thái lớp học
        //         DB::table('classrooms')
        //             ->where('id', $classroom->id)
        //             ->update(['is_active' => false]);
        //     }
        // }
        $startDate = Carbon::parse($request->input('startDate')); // Ngày bắt đầu từ request
        $startDates = []; // Mảng chứa các ngày cần lấy
        DB::table('classrooms')
            ->where(function ($query) {
                $query->where('classrooms.is_active', true)
                    ->where('classrooms.is_automatic', false);
            })
            ->orWhereDate('classrooms.created_at', '=', date('Y-m-d'))
            ->delete();
        // Thêm ngày hiện tại vào mảng
        $startDates[] = $startDate->format('Y-m-d');

        // Lấy ngày tiếp theo, bỏ Chủ nhật
        $startDate->addDay(); // Thêm 1 ngày

        // Kiểm tra xem ngày tiếp theo có phải là Chủ nhật không
        if ($startDate->dayOfWeek != Carbon::SUNDAY) {
            $startDates[] = $startDate->format('Y-m-d');
        } else {
            // Nếu là Chủ nhật thì thêm 1 ngày nữa (là thứ 2 tuần sau)
            $startDate->addDay();
            $startDates[] = $startDate->format('Y-m-d');
        }
        if (!$startDates || !is_array($startDates)) {
            return response()->json(['error' => true, 'message' => 'Ngày không đúng định dạng mảng'], 400);
        }

        $schoolRoom = DB::table('categories')->where('type', '=', "school_room")->where('is_active', '=', true)->get();
        $sessions = DB::table('categories')->where('type', '=', "session")->where('is_active', '=', true)->get();
        $index = 1;
        $createdClassrooms = [];
        // return response()->json([$sessions]);

        foreach ($schoolRoom as $room) {
            foreach ($sessions as $session) {
                foreach ($startDates as $startDate) {
                    try {
                        $date = Carbon::parse($startDate);
                        $timestamp = $date->timestamp;
                    } catch (\Exception $e) {
                        return response()->json(['error' => true, 'message' => "Ngày không hợp lệ: $startDate"]);
                    }

                    $classroom = Classroom::firstOrCreate([
                        'class_code' => $timestamp . "_" . $session->cate_code . "_" . $room->cate_code . "_" . $index,
                        'class_name' => $timestamp . "_" . $session->cate_code . "_" . $room->cate_code . "_" . $index,
                        'is_active' => true
                    ]);

                    $createdClassrooms[] = $classroom;

                    Schedule::firstOrCreate([
                        'class_code' => $timestamp . "_" . $session->cate_code . "_" . $room->cate_code . "_" . $index,
                        'room_code' => $room->cate_code,
                        'session_code' => $session->cate_code,
                        'date' => $date->format('Y-m-d')
                    ]);

                    // return dd($dayOfWeek . "_" . $room->cate_code . "_" . $session->cate_code . "_" . $index);
                }
            }
            $index++;
        }




        return response()->json([
            'count' => count($createdClassrooms),
            'startDates' => $startDates
        ]);
        // } catch (\Throwable $th) {
        //     return response()->json([
        //         'error' => false,
        //         'message' => 'Có lỗi xảy ra. Tạm dừng tạo lớp tự động',
        //     ], 400);
        // }
    }

    // public function getStudentsInSameClassOrSession($sessionCode)
    // {
    //     $user =  DB::table('classroom_user as cu')
    //         ->join('classrooms as c', 'cu.class_code', '=', 'c.class_code')
    //         ->join('schedules as d', 'd.class_code', '=', 'c.class_code')
    //         ->where('d.session_code', $sessionCode)
    //         ->select('cu.user_code', 'cu.class_code', 'd.session_code', 'd.room_code')
    //         ->distinct()
    //         ->get();
    //     return $user;
    // }


    // public function checkStudentScheduleConflict($userCode, $classCode, $roomCode, $sessionCode, $date)
    // {
    //     // Kiểm tra xem sinh viên đã có mặt trong lớp này chưa
    //     $alreadyEnrolled = DB::table('classroom_user')
    //         ->where('class_code', $classCode)
    //         ->where('user_code', $userCode)
    //         ->exists();

    //     if ($alreadyEnrolled) {
    //         return 'Đã xếp lớp';
    //     }

    //     // Kiểm tra xem sinh viên có trùng ca học trong cùng ngày và phòng không
    //     $scheduleConflict = DB::table('schedules')
    //         ->join('classroom_user', 'schedules.class_code', '=', 'classroom_user.class_code')
    //         ->where('classroom_user.user_code', $userCode)
    //         ->where('schedules.room_code', $roomCode)
    //         ->where('schedules.session_code', $sessionCode)
    //         ->where('schedules.date', $date)
    //         ->exists();

    //     return $scheduleConflict ? 'Trùng ca học' : 'Không trùng';
    // }


    // private function assignClasses($listHocSinh, $listPhonghoc, $listMonHoc, $daysOfWeek)
    // {
    //     $listLop = []; // Danh sách lớp học đã xếp
    //     $classTimes = $this->generateClassTimes(); // Danh sách ca học

    //     // Lưu trạng thái hiện tại của ngày, ca, và phòng cho mỗi môn
    //     $currentDayIndex = 0;
    //     $currentRoomIndex = 0;
    //     $currentTimeIndex = 0;

    //     // Lưu số buổi dạy của từng giảng viên
    //     $teacherWorkload = [];

    //     // Giữ phòng hiện tại của từng giảng viên
    //     $teacherRoom = [];

    //     // Duyệt qua từng môn học
    //     foreach ($listMonHoc as $mon) {
    //         // Bộ đếm lớp học cho mỗi môn
    //         $classCounter = 1;

    //         // Lọc danh sách học sinh theo chuyên ngành của môn học
    //         $studentsInClass = array_filter($listHocSinh, function ($hs) use ($mon) {
    //             return $hs['chuyenNganh'] === $mon['chuyenNganh']; // Lọc theo chuyên ngành
    //         });

    //         // Lấy danh sách giảng viên theo chuyên ngành của môn học
    //         $teachersInMajor = $this->generateTeacherList();
    //         // return dd($teachersInMajor);
    //         $teachersForClass = array_filter($teachersInMajor, function ($gv) use ($mon) {
    //             return $gv['chuyenNganh'] === $mon['chuyenNganh']; // Lọc giảng viên theo chuyên ngành
    //         });

    //         // Sắp xếp giảng viên theo số buổi dạy hiện tại, ưu tiên những người có ít buổi dạy hơn
    //         usort($teachersForClass, function ($a, $b) use ($teacherWorkload) {
    //             return ($teacherWorkload[$a['maGV']] ?? 0) <=> ($teacherWorkload[$b['maGV']] ?? 0);
    //         });

    //         // Chọn giảng viên
    //         $currentTeacher = reset($teachersForClass);

    //         // Nếu giảng viên đã có phòng, ưu tiên giữ nguyên phòng
    //         if (isset($teacherRoom[$currentTeacher['maGV']])) {
    //             $currentRoomIndex = array_search($teacherRoom[$currentTeacher['maGV']], array_column($listPhonghoc, 'code'));
    //         }

    //         $currentStudentIndex = 0; // Chỉ số học sinh hiện tại
    //         $totalStudents = count($studentsInClass); // Tổng số học sinh cho môn này

    //         // Tiếp tục vòng lặp qua các ngày, ca, và phòng học, bắt đầu từ trạng thái hiện tại
    //         while ($currentStudentIndex < $totalStudents) {
    //             // Kiểm tra nếu đã hết các ngày trong tuần
    //             if ($currentDayIndex >= count($daysOfWeek)) {
    //                 break; // Dừng lại khi đã hết các ngày
    //             }

    //             // Lấy ngày hiện tại
    //             $day = $daysOfWeek[$currentDayIndex];

    //             // Lấy phòng học hiện tại
    //             $phong = $listPhonghoc[$currentRoomIndex];

    //             // Lấy ca học hiện tại
    //             $classTime = $classTimes[$currentTimeIndex];

    //             // Sức chứa của phòng học
    //             $roomCapacity = $phong['sucChua'];

    //             // Số lượng học sinh tối đa trong lớp là sức chứa của phòng hoặc số học sinh còn lại
    //             $classSize = min($roomCapacity, $totalStudents - $currentStudentIndex);

    //             // Tạo tên lớp, ví dụ: "Lớp MH101 - 1"
    //             $className = "Lớp " . $mon['code'] . " - " . $classCounter;

    //             // Tạo lớp cho môn học trong phòng này, ngày này, ca này
    //             $listLop[] = [
    //                 "tenLop" => $className, // Tên lớp
    //                 "monHoc" => $mon['code'],
    //                 "phongHoc" => $phong['code'],
    //                 "ngay" => $day['code'], // Ngày học
    //                 "ca" => $classTime['code'], // Ca học
    //                 "giangVien" => $currentTeacher['ten'], // Giảng viên dạy
    //                 "hocSinh" => array_slice($studentsInClass, $currentStudentIndex, $classSize), // Danh sách học sinh trong lớp
    //             ];

    //             // Cập nhật chỉ số học sinh đã xếp vào lớp
    //             $currentStudentIndex += $classSize;

    //             // Tăng số buổi dạy của giảng viên
    //             if (!isset($teacherWorkload[$currentTeacher['maGV']])) {
    //                 $teacherWorkload[$currentTeacher['maGV']] = 0;
    //             }
    //             $teacherWorkload[$currentTeacher['maGV']]++;

    //             // Lưu lại phòng hiện tại của giảng viên
    //             $teacherRoom[$currentTeacher['maGV']] = $phong['code'];

    //             // Tăng bộ đếm lớp cho môn học
    //             $classCounter++;

    //             // Cập nhật chỉ số ca học
    //             $currentTimeIndex++;

    //             // Nếu đã hết các ca trong ngày, chuyển sang phòng học tiếp theo
    //             if ($currentTimeIndex >= count($classTimes)) {
    //                 $currentTimeIndex = 0;
    //                 // Kiểm tra phòng kế tiếp
    //                 $nextRoom = $this->findNextRoom($listPhonghoc, $phong, $currentRoomIndex);
    //                 $currentRoomIndex = array_search($nextRoom['code'], array_column($listPhonghoc, 'code'));
    //             }

    //             // Nếu đã hết các phòng trong ngày, chuyển sang ngày tiếp theo
    //             if ($currentRoomIndex >= count($listPhonghoc)) {
    //                 $currentRoomIndex = 0;
    //                 $currentDayIndex++;
    //             }

    //             // Kiểm tra nếu đã hết các ngày trong tuần và thoát khỏi vòng lặp
    //             if ($currentDayIndex >= count($daysOfWeek)) {
    //                 break; // Dừng lại khi đã hết các ngày trong tuần
    //             }
    //         }
    //     }

    //     return $listLop;
    // }

    // Hàm tìm phòng học liền kề hoặc giữ nguyên phòng
    // private function findNextRoom($listPhonghoc, $currentRoom, $currentRoomIndex)
    // {
    //     $currentRoomCode = intval(preg_replace('/\D/', '', $currentRoom['code'])); // Lấy mã số phòng hiện tại (chỉ lấy số)

    //     // Tìm phòng học liền kề (phòng kế tiếp hoặc giữ nguyên nếu không có phòng liền kề)
    //     foreach ($listPhonghoc as $room) {
    //         $roomCode = intval(preg_replace('/\D/', '', $room['code']));
    //         if ($roomCode === $currentRoomCode + 1 || $roomCode === $currentRoomCode - 1) {
    //             return $room; // Trả về phòng liền kề
    //         }
    //     }

    //     return $currentRoom; // Nếu không tìm thấy phòng liền kề, giữ nguyên phòng hiện tại
    // }


    private function generateClassTimes()
    {

        $sessions = DB::table('categories')->where('type', '=', "session")->where('is_active', '=', true)->get();
        $classTimes = $sessions->map(function ($session) {
            return [
                'code' => $session->cate_code,
                'name' => $session->cate_name
            ];
        })->toArray();
        return $classTimes;
    }

    // private function getListUserByClassRooms($sectionCode)
    // {

    //     // $classrooms = DB::table('classrooms')->where('room_code', '=', $roomCode)->where('subject_code', '=', $subjectCode)->where('section_code', '=', $sectionCode)->where('is_active', '=', true)->get();
    //     $classrooms = DB::table('classrooms')->where('section_code', '=', $sectionCode)->where('is_active', '=', true)->get();
    //     $class = $classrooms->map(function ($classroom) {
    //         return [
    //             'code' => $classroom->class_code,
    //             'name' => $classroom->class_name,
    //             'students' => $classroom->students,
    //             'teacher' => $classroom->user_code,
    //         ];
    //     })->toArray();
    //     return $class;
    // }
}
