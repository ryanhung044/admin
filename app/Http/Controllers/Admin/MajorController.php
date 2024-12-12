<?php

namespace App\Http\Controllers\Admin;

use Throwable;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Major\StoreMajorRequest;
use App\Http\Requests\Major\UpdateMajorRequest;
use App\Models\User;

class MajorController extends Controller
{
    // Hàm trả về json khi id không hợp lệ
    public function handleInvalidId()
    {

        return response()->json([
            'message' => 'Không có chuyên ngành nào!',
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
            // Lấy ra cate_code và cate_name của cha
            $search = $request->input('search');
            $perPage = $request->input('per_page', 10);
            $majors = Category::with(
                ['childrens' => function ($query) {
                    $query->select('cate_code', 'cate_name', 'image', 'parent_code', 'is_active');
                }]
            )->whereNull('parent_code')
                ->where('type', '=', 'major')
                ->select('cate_code', 'cate_name', 'image', 'is_active')->when($search, function ($query, $search) {
                    return $query->where('cate_name', 'like', "%$search%")->orWhereHas("childrens", function ($childQuerry) use ($search) {
                        $childQuerry->where('cate_name', 'like', "%$search%");
                    });
                })->paginate($perPage);
            return response()->json($majors, 200);
        } catch (Throwable $th) {
            return $this->handleErrorNotDefine($th);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMajorRequest $request)
    {
        try {
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
            // Lấy ra cate_code và cate_name của cha
            $parent = Category::whereNull('parent_code')
                ->where('type', '=', 'major')
                ->select('cate_code', 'cate_name')
                ->get();
            $listMajor = Category::where('cate_code', $cate_code)->first();
            if (!$listMajor) {

                return $this->handleInvalidId();
            } else {

                return response()->json([
                    'parent' => $parent,
                    'listMajor' => $listMajor
                ], 200);
            }
        } catch (\Throwable $th) {

            return $this->handleErrorNotDefine($th);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMajorRequest $request, string $cate_code)
    {
        // return response()->json($request, 201);

        DB::beginTransaction();
        // try {
        $listMajor = Category::where('cate_code', $cate_code)->lockForUpdate()->first();

        if (!$listMajor) {
            DB::rollBack();

            return $this->handleInvalidId();
        } else {
            $params = $request->except('_token', '_method');
            // Kiểm tra parent_code hợp lệ hoặc null

            if ($request->hasFile('image')) {
                if ($listMajor->image && Storage::disk('public')->exists($listMajor->image)) {
                    Storage::disk('public')->delete($listMajor->image);
                }
                $fileName = $request->file('image')->store('uploads/image', 'public');
            } else {
                $fileName = $listMajor->image;
            }
            $params['image'] = $fileName;
            $listMajor->is_active =  $params['is_active'];
            $listMajor->update($params);
            DB::commit();

            return response()->json($listMajor, 201);
        }
        // } catch (Throwable $th) {

        //     return $this->handleErrorNotDefine($th);
        // }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $cate_code)
    {
        DB::beginTransaction();
        try {
            $listMajor = Category::where('cate_code', $cate_code)->lockForUpdate()->first();
            if (!$listMajor) {
                DB::rollBack();

                return $this->handleInvalidId();
            } else {
                if ($listMajor->image && Storage::disk('public')->exists($listMajor->image)) {
                    Storage::disk('public')->delete($listMajor->image);
                }
                $listMajor->delete($listMajor);
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

    // public function getListMajor(string $type)
    // {
    //     // Lấy tất cả danh mục cha
    //     // dd($type);
    //     $categories = DB::table('categories')
    //         ->where('type', '=', $type)
    //         ->where('parent_code', '=', "")
    //         // ->whereNull('parent_code')
    //         ->get();
    //     // dd($categories);
    //     // return;

    //     // Duyệt qua từng danh mục cha để lấy danh mục con
    //     $data = $categories->map(function ($category) {
    //         // Lấy danh mục con dựa trên parent_code
    //         $subCategories = DB::table('categories')
    //             ->where('parent_code', '=', $category->cate_code)
    //             ->get();

    //         // Trả về cấu trúc dữ liệu theo yêu cầu
    //         return [
    //             'id' => $category->id,
    //             'cate_code' => $category->cate_code,
    //             'cate_name' => $category->cate_name,
    //             'image' => $category->image,
    //             'description' => $category->description,
    //             'listItem'  => $subCategories
    //         ];
    //     });

    //     //Cách 2

    //     return response()->json($data);
    // }



    // Không được xoá của Hải! Cái này liên quan đến classroom
    public function renderTeachersAvailable(string $major_code)
    {
        try {
            $teachers = User::where([
                'is_active' => true,
                'role' => 'teacher',
                'major_code' => $major_code
            ])->select('full_name', 'user_code')->get();

            if ($teachers->isEmpty()) {
                return response()->json([
                    'message' => 'Không có giảng viên nào hợp lệ'
                ], 404);
            }
            return response()->json($teachers, 200);
        } catch (\Throwable $th) {
            return $this->handleErrorNotDefine($th);
        }
    }
}
