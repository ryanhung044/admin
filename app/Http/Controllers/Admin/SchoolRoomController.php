<?php

namespace App\Http\Controllers\Admin;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\SchoolRoom\StoreSchoolRoomRequest;
use App\Http\Requests\SchoolRoom\UpdateSchoolRoomRequest;

class SchoolRoomController extends Controller
{
    // Hàm trả về json khi id không hợp lệ
    public function handleInvalidId()
    {

        return response()->json([
            'message' => 'Không có Phòng Học nào!',
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
            // Tìm kiếm theo cate_name
            $search = $request->input('search');
            $perPage = $request->input('per_page', 10);
            $data = Category::where('type', '=', 'school_room')
                ->when($search, function ($query, $search) {
                    return $query
                        ->where('cate_name', 'like', "%{$search}%");
                })
                ->orderBy('id', 'desc')
                ->paginate($perPage);
            if ($data->isEmpty()) {

                return $this->handleInvalidId();
            }


            return response()->json($data, 200);
        } catch (\Throwable $th) {

            return $this->handleErrorNotDefine($th);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSchoolRoomRequest $request)
    {
        try {
            // Lấy ra cate_code và cate_name của cha
            $parent = Category::whereNull('parent_code')
                ->where('type', '=', 'school_room')
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
        } catch (\Throwable $th) {

            return $this->handleErrorNotDefine($th);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(string $cate_code)
    {
        try {
            $schoolRoom = Category::where('cate_code', $cate_code)->first();
            if (!$schoolRoom) {
                return $this->handleInvalidId();
            } else {

                return response()->json($schoolRoom, 200);
            }
        } catch (\Throwable $th) {

            return $this->handleErrorNotDefine($th);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSchoolRoomRequest $request, string $cate_code)
    {
        DB::beginTransaction();
        try {
            $listSchoolRoom = Category::where('cate_code', $cate_code)->lockForUpdate()->first();
            if (!$listSchoolRoom) {
                DB::rollBack();
                return $this->handleInvalidId();
            } else {
                // return response()->json($request->all(), 201);
                $params = $request->except('_token', '_method');
                if ($request->hasFile('image')) {
                    if ($listSchoolRoom->image && Storage::disk('public')->exists($listSchoolRoom->image)) {
                        Storage::disk('public')->delete($listSchoolRoom->image);
                    }
                    $fileName = $request->file('image')->store('uploads/image', 'public');
                } else {
                    $fileName = $listSchoolRoom->image;
                }
                $params['image'] = $fileName;
                $listSchoolRoom->update($params);
                // DB::rollBack();
                DB::commit();

                return response()->json($listSchoolRoom, 201);
            }
        } catch (\Throwable $th) {

            return $this->handleErrorNotDefine($th);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $cate_code)
    {
        DB::beginTransaction();
        try {
            $listSchoolRoom = Category::where('cate_code', $cate_code)->lockForUpdate()->first();
            if (!$listSchoolRoom) {
                DB::rollBack();
                return $this->handleInvalidId();
            } else {
                if ($listSchoolRoom->image && Storage::disk('public')->exists($listSchoolRoom->image)) {
                    Storage::disk('public')->delete($listSchoolRoom->image);
                }
                $listSchoolRoom->delete();
                DB::commit();

                return response()->json([
                    'message' => 'Xóa thành công'
                ], 200);
            }
        } catch (\Throwable $th) {

            return $this->handleErrorNotDefine($th);
        }
    }

    public function bulkUpdateType(Request $request)
    {
        try {
            $activies = $request->input('is_active'); // Lấy dữ liệu từ request

            DB::transaction(function () use ($activies) {
                foreach ($activies as $cate_code => $active) {
                    // Tìm category theo cate_code và áp dụng lock for update
                    $category = Category::where('cate_code', $cate_code)->lockForUpdate()->first();

                    if ($category) {
                        $category->is_active = $active; // Sửa lại đúng field
                        $category->save();
                    }
                }
            });

            return response()->json([
                'message' => 'Trạng thái đã được cập nhật thành công!'
            ], 200);
        } catch (\Throwable $th) {
            return $this->handleErrorNotDefine($th);
        }
    }
}
