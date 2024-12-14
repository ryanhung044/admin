<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $courses = Category::where('type', 'course')
                            ->where('is_active', 1)
                            ->get();

        return response()->json($courses);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $first_year     = $request->first_year;
        $final_year     = $request->final_year;
        $course_number  = $request->course_number;

        if (!is_numeric($first_year) || !is_numeric($final_year) || $first_year >= $final_year) {
            return response()->json(["message" => "Năm bắt đầu và kết thúc không hợp lệ."], 400);
        }

        $cate_code = "K".$course_number;
        $cate_name = "Khóa ".$course_number;
        $course_year = $first_year . '-' . $final_year;

        if (Category::where('cate_code', $cate_code)->exists()) {
            return response()->json(["message" => "Mã khóa học đã tồn tại."], 400);
        }

        $course = Category::create([
            'cate_code' => $cate_code,
            'cate_name' => $cate_name,
            'value'     => $course_year,
            'type'      => 'course',
            'is_active' => 1,
        ]);

        return response()->json($course, 201);
    }


    public function update(Request $request, $code)
    {
        $course = Category::where('cate_code',$code);

        if (!$course) {
            return response()->json(["message" => "Khóa học không tồn tại."], 404);
        }

        $first_year = $request->first_year;
        $final_year = $request->final_year;
        $course_number = $request->course_number;

        $cate_code = "K".$course_number;
        $cate_name = "Khóa ".$course_number;
        // 3. Tạo mã và tên khóa học mới
        $course_year = $first_year . '-' . $final_year;

        // 4. Kiểm tra trùng mã khóa học (ngoại trừ chính khóa học đang cập nhật)
        if (Category::where('cate_code', $cate_code)->where('cate_code', '!=', $cate_code)->exists()) {
            return response()->json(["message" => "Mã khóa học đã tồn tại."], 400);
        }

        // 5. Cập nhật dữ liệu
        $course->update([
            'cate_code' => $cate_code,
            'cate_name' => $cate_name,
            'value'     => $course_year,
        ]);

        return response()->json(["message" => "Cập nhật thành công.", "data" => $course], 200);
    }

    public function show(string $code)
    {
        $course = Category::where('type', 'course')->where('cate_code',$code)->get();
        if($course->isEmpty()){
            return response()->json(['message'=>'không tìm thấy']);
        }
        return response()->json($course);
    }

    public function destroy(string $code)
    {
        $course = Category::where('cate_code',$code);
        $course->delete();
        return response()->json(['message' => 'Xóa thành công'], 200);
    }
}
